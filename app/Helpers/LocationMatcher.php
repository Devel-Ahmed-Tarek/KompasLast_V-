<?php

namespace App\Helpers;

use App\Models\City;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Collection;

class LocationMatcher
{
    /**
     * Earth radius in kilometers.
     */
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Distance in km between two coordinates (Haversine).
     * Returns null if any coordinate is missing.
     */
    public static function distanceKm(
        ?float $lat1,
        ?float $lng1,
        ?float $lat2,
        ?float $lng2
    ): ?float {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Distance between two City models.
     */
    public static function distanceBetweenCities(?City $from, ?City $to): ?float
    {
        if (! $from || ! $to) {
            return null;
        }

        return self::distanceKm(
            $from->latitude,
            $from->longitude,
            $to->latitude,
            $to->longitude
        );
    }

    /**
     * Does a company subscription city cover an offer city?
     *
     * Rules:
     * - Same city_id → always match
     * - radius_km = 0 → exact city only
     * - radius_km > 0 → match if Haversine distance ≤ radius_km
     * - Missing coordinates → exact city only (legacy fallback)
     */
    public static function cityCovers(
        City $companyCity,
        City $offerCity,
        int $radiusKm = 0
    ): bool {
        if ((int) $companyCity->id === (int) $offerCity->id) {
            return true;
        }

        if ($radiusKm <= 0) {
            return false;
        }

        $distance = self::distanceBetweenCities($companyCity, $offerCity);

        if ($distance === null) {
            return false;
        }

        return $distance <= $radiusKm;
    }

    /**
     * Does this company cover the offer location (country + city with radius)?
     */
    public static function companyCoversOffer(User $company, Offer $offer): bool
    {
        if (! $offer->country_id || ! $offer->city_id) {
            return false;
        }

        $subscribedCountryIds = $company->countries()
            ->pluck('countries.id')
            ->all();

        if (! in_array((int) $offer->country_id, array_map('intval', $subscribedCountryIds), true)) {
            return false;
        }

        $offerCity = $offer->relationLoaded('cityRelation')
            ? $offer->cityRelation
            : City::find($offer->city_id);

        if (! $offerCity) {
            return false;
        }

        $companyCities = $company->relationLoaded('cities')
            ? $company->cities
            : $company->cities()->get();

        foreach ($companyCities as $companyCity) {
            $radiusKm = (int) ($companyCity->pivot->radius_km ?? 0);

            if (self::cityCovers($companyCity, $offerCity, $radiusKm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter companies that cover a given offer city (within country + radius).
     *
     * @param  Collection<int, User>  $companies
     * @return Collection<int, User>
     */
    public static function filterCompaniesForOffer(Collection $companies, Offer $offer): Collection
    {
        return $companies->filter(function (User $company) use ($offer) {
            return self::companyCoversOffer($company, $offer);
        })->values();
    }

    /**
     * City IDs that fall within a company's coverage (exact + radius).
     * Useful for shop queries: whereIn('city_id', ...).
     *
     * @return array<int>
     */
    public static function coveredCityIdsForCompany(User $company): array
    {
        $companyCities = $company->relationLoaded('cities')
            ? $company->cities
            : $company->cities()->get();

        if ($companyCities->isEmpty()) {
            return [];
        }

        $coveredIds = [];

        foreach ($companyCities as $companyCity) {
            $coveredIds[] = (int) $companyCity->id;

            $radiusKm = (int) ($companyCity->pivot->radius_km ?? 0);
            if ($radiusKm <= 0) {
                continue;
            }

            if ($companyCity->latitude === null || $companyCity->longitude === null) {
                continue;
            }

            // Candidate cities in the same country (keep matching scoped)
            $candidates = City::query()
                ->where('country_id', $companyCity->country_id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('id', '!=', $companyCity->id)
                ->get();

            foreach ($candidates as $candidate) {
                if (self::cityCovers($companyCity, $candidate, $radiusKm)) {
                    $coveredIds[] = (int) $candidate->id;
                }
            }
        }

        return array_values(array_unique($coveredIds));
    }
}
