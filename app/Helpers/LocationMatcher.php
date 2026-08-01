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
     * Find the nearest city that has coordinates.
     * Optionally limit search to one country.
     */
    public static function findNearestCity(float $lat, float $lng, ?int $countryId = null): ?City
    {
        $query = City::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $cities = $query->get();
        if ($cities->isEmpty()) {
            return null;
        }

        $nearest = null;
        $minDistance = null;

        foreach ($cities as $city) {
            $distance = self::distanceKm($lat, $lng, $city->latitude, $city->longitude);
            if ($distance === null) {
                continue;
            }

            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $city;
            }
        }

        return $nearest;
    }

    /**
     * Resolve offer location from select and/or coordinates.
     *
     * Rules:
     * - If latitude+longitude sent without city_id → nearest city (auto country/city)
     * - If city_id (+ country_id) sent → use select; coords optional for precise matching
     * - If neither coords nor city select → error
     *
     * @param  array{country_id?: mixed, city_id?: mixed, latitude?: mixed, longitude?: mixed}  $input
     * @return array{country_id: int|null, city_id: int|null, latitude: float|null, longitude: float|null, error: string|null}
     */
    public static function resolveOfferLocation(array $input): array
    {
        $hasLat = array_key_exists('latitude', $input) && $input['latitude'] !== null && $input['latitude'] !== '';
        $hasLng = array_key_exists('longitude', $input) && $input['longitude'] !== null && $input['longitude'] !== '';
        $hasCoords = $hasLat && $hasLng;

        $countryId = ! empty($input['country_id']) ? (int) $input['country_id'] : null;
        $cityId = ! empty($input['city_id']) ? (int) $input['city_id'] : null;

        $latitude = $hasLat ? (float) $input['latitude'] : null;
        $longitude = $hasLng ? (float) $input['longitude'] : null;

        if ($hasLat xor $hasLng) {
            return [
                'country_id' => null,
                'city_id'    => null,
                'latitude'   => null,
                'longitude'  => null,
                'error'      => 'Both latitude and longitude are required together.',
            ];
        }

        // Path A: coords only (or coords + optional country filter) → nearest city
        if ($hasCoords && ! $cityId) {
            $nearest = self::findNearestCity($latitude, $longitude, $countryId);
            if (! $nearest) {
                return [
                    'country_id' => null,
                    'city_id'    => null,
                    'latitude'   => $latitude,
                    'longitude'  => $longitude,
                    'error'      => 'No nearby city with coordinates was found.',
                ];
            }

            return [
                'country_id' => (int) $nearest->country_id,
                'city_id'    => (int) $nearest->id,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'error'      => null,
            ];
        }

        // Path B: select required when no city from coords path
        if (! $cityId || ! $countryId) {
            return [
                'country_id' => null,
                'city_id'    => null,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'error'      => 'country_id and city_id are required when coordinates are not provided.',
            ];
        }

        $city = City::find($cityId);
        if (! $city) {
            return [
                'country_id' => null,
                'city_id'    => null,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'error'      => 'City not found.',
            ];
        }

        if ((int) $city->country_id !== $countryId) {
            return [
                'country_id' => null,
                'city_id'    => null,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'error'      => 'The selected city does not belong to the selected country.',
            ];
        }

        return [
            'country_id' => $countryId,
            'city_id'    => $cityId,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'error'      => null,
        ];
    }

    /**
     * Does a company subscription city cover an offer city / offer point?
     *
     * Rules:
     * - Same city_id → always match
     * - radius_km = 0 → exact city only
     * - radius_km > 0 → match if distance ≤ radius_km
     * - Prefer offer lat/lng when present; else city-to-city
     */
    public static function cityCovers(
        City $companyCity,
        City $offerCity,
        int $radiusKm = 0,
        ?float $offerLat = null,
        ?float $offerLng = null
    ): bool {
        if ((int) $companyCity->id === (int) $offerCity->id) {
            return true;
        }

        if ($radiusKm <= 0) {
            return false;
        }

        if ($offerLat !== null && $offerLng !== null) {
            $distance = self::distanceKm(
                $companyCity->latitude,
                $companyCity->longitude,
                $offerLat,
                $offerLng
            );
        } else {
            $distance = self::distanceBetweenCities($companyCity, $offerCity);
        }

        if ($distance === null) {
            return false;
        }

        return $distance <= $radiusKm;
    }

    /**
     * Does this company cover the offer location (country + city/radius)?
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

        $offerLat = $offer->latitude !== null ? (float) $offer->latitude : null;
        $offerLng = $offer->longitude !== null ? (float) $offer->longitude : null;

        foreach ($companyCities as $companyCity) {
            $radiusKm = (int) ($companyCity->pivot->radius_km ?? 0);

            if (self::cityCovers($companyCity, $offerCity, $radiusKm, $offerLat, $offerLng)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter companies that cover a given offer.
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
