# Get Cities by Country API Documentation

## Overview
This endpoint allows you to get all cities that belong to a specific country. When a user selects a country, this endpoint returns all cities within that country.

**Base URLs:**
- Company API: `/api/company`
- Admin API: `/api/admin`

**Authentication:** Required (Bearer Token - Sanctum)

---

## Company API Endpoint

### Get Cities by Country

**Endpoint:** `GET /api/company/cities/by-country/{country_id}`

**Method:** `GET`

**URL Parameters:**
- `country_id` (integer, required) - The ID of the country

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```
GET /api/company/cities/by-country/1
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response (200 - Success):**
```json
{
    "status": 200,
    "message": "Cities fetched successfully",
    "data": {
        "country": {
            "id": 1,
            "name": {
                "en": "Germany",
                "ar": "ألمانيا",
                "de": "Deutschland",
                "fr": "Allemagne",
                "it": "Germania"
            }
        },
        "cities": [
            {
                "id": 1,
                "country_id": 1,
                "name": {
                    "en": "Berlin",
                    "ar": "برلين",
                    "de": "Berlin",
                    "fr": "Berlin",
                    "it": "Berlino"
                },
                "created_at": "2026-01-19T15:33:55.000000Z",
                "updated_at": "2026-01-19T15:33:55.000000Z"
            },
            {
                "id": 2,
                "country_id": 1,
                "name": {
                    "en": "Hamburg",
                    "ar": "هامبورغ",
                    "de": "Hamburg",
                    "fr": "Hambourg",
                    "it": "Amburgo"
                },
                "created_at": "2026-01-19T15:33:55.000000Z",
                "updated_at": "2026-01-19T15:33:55.000000Z"
            },
            {
                "id": 3,
                "country_id": 1,
                "name": {
                    "en": "Munich",
                    "ar": "ميونيخ",
                    "de": "München",
                    "fr": "Munich",
                    "it": "Monaco di Baviera"
                },
                "created_at": "2026-01-19T15:33:55.000000Z",
                "updated_at": "2026-01-19T15:33:55.000000Z"
            }
            // ... جميع مدن الدولة
        ]
    }
}
```

**Error Response (404 - Country Not Found):**
```json
{
    "status": 404,
    "message": "Country not found",
    "data": []
}
```

**Error Response (422 - Validation Error):**
```json
{
    "status": 422,
    "message": "Validation errors",
    "data": {
        "country_id": [
            "The selected country id is invalid."
        ]
    }
}
```

---

## Admin API Endpoint

### Get Cities by Country

**Endpoint:** `GET /api/admin/cities/by-country/{country_id}`

**Method:** `GET`

**URL Parameters:**
- `country_id` (integer, required) - The ID of the country

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```
GET /api/admin/cities/by-country/1
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response:** Same as Company API endpoint above.

---

## Use Cases

### 1. Dropdown Selection Flow
When a user selects a country from a dropdown, use this endpoint to populate the cities dropdown:

```javascript
// User selects country with ID = 1
const countryId = 1;

// Fetch all cities for that country
const response = await axios.get(`/api/company/cities/by-country/${countryId}`, {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

// Populate cities dropdown
const cities = response.data.data.cities;
cities.forEach(city => {
    // Add city to dropdown
    console.log(city.name.en); // "Berlin", "Hamburg", etc.
});
```

### 2. Display Country with All Cities
Show a country and list all its cities:

```javascript
const response = await axios.get('/api/company/cities/by-country/1', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const country = response.data.data.country;
const cities = response.data.data.cities;

console.log(`Country: ${country.name.en}`);
console.log(`Total Cities: ${cities.length}`);
cities.forEach(city => {
    console.log(`- ${city.name.en}`);
});
```

### 3. Filter Cities by Country
Before showing available cities to subscribe, filter by selected country:

```javascript
// Step 1: User selects country
const selectedCountryId = 1;

// Step 2: Get all cities in that country
const citiesResponse = await axios.get(`/api/company/cities/by-country/${selectedCountryId}`, {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

// Step 3: Show only cities from selected country
const countryCities = citiesResponse.data.data.cities;

// Step 4: Get available cities (already filtered by country)
const availableResponse = await axios.get('/api/company/cities/available', {
    params: { country_id: selectedCountryId },
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

// Compare and show which cities are available to subscribe
```

---

## Example Usage (JavaScript/Axios)

### Basic Usage
```javascript
import axios from 'axios';

const getCitiesByCountry = async (countryId, token) => {
    try {
        const response = await axios.get(
            `/api/company/cities/by-country/${countryId}`,
            {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            }
        );
        
        return response.data.data;
    } catch (error) {
        console.error('Error fetching cities:', error.response?.data);
        throw error;
    }
};

// Usage
const data = await getCitiesByCountry(1, 'your-token');
console.log('Country:', data.country.name.en);
console.log('Cities:', data.cities);
```

### React Hook Example
```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

const useCitiesByCountry = (countryId, token) => {
    const [cities, setCities] = useState([]);
    const [country, setCountry] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!countryId) return;

        const fetchCities = async () => {
            setLoading(true);
            setError(null);
            
            try {
                const response = await axios.get(
                    `/api/company/cities/by-country/${countryId}`,
                    {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    }
                );
                
                setCountry(response.data.data.country);
                setCities(response.data.data.cities);
            } catch (err) {
                setError(err.response?.data?.message || 'Failed to fetch cities');
            } finally {
                setLoading(false);
            }
        };

        fetchCities();
    }, [countryId, token]);

    return { country, cities, loading, error };
};

// Usage in component
function CitySelector({ countryId, token }) {
    const { country, cities, loading, error } = useCitiesByCountry(countryId, token);

    if (loading) return <div>Loading cities...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <div>
            <h3>{country?.name.en}</h3>
            <select>
                {cities.map(city => (
                    <option key={city.id} value={city.id}>
                        {city.name.en}
                    </option>
                ))}
            </select>
        </div>
    );
}
```

### Vue.js Example
```javascript
export default {
    data() {
        return {
            country: null,
            cities: [],
            loading: false,
            error: null
        };
    },
    methods: {
        async fetchCitiesByCountry(countryId) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get(
                    `/api/company/cities/by-country/${countryId}`,
                    {
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                        }
                    }
                );
                
                this.country = response.data.data.country;
                this.cities = response.data.data.cities;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch cities';
            } finally {
                this.loading = false;
            }
        }
    },
    watch: {
        selectedCountryId(newId) {
            if (newId) {
                this.fetchCitiesByCountry(newId);
            }
        }
    }
};
```

---

## Response Structure

### Success Response
```typescript
interface GetCitiesByCountryResponse {
    status: 200;
    message: "Cities fetched successfully";
    data: {
        country: {
            id: number;
            name: {
                en: string;
                ar: string;
                de: string;
                fr: string;
                it: string;
            };
        };
        cities: Array<{
            id: number;
            country_id: number;
            name: {
                en: string;
                ar: string;
                de: string;
                fr: string;
                it: string;
            };
            created_at: string;
            updated_at: string;
        }>;
    };
}
```

### Error Response
```typescript
interface ErrorResponse {
    status: 404 | 422;
    message: string;
    data: object | array;
}
```

---

## Integration with Other Endpoints

This endpoint works seamlessly with other countries/cities endpoints:

### Complete Flow Example

```javascript
// Step 1: Get all available countries
const countriesResponse = await axios.get('/api/company/countries/available');
const countries = countriesResponse.data.data;

// Step 2: User selects a country (e.g., Germany with ID = 1)
const selectedCountryId = 1;

// Step 3: Get all cities in selected country
const citiesResponse = await axios.get(`/api/company/cities/by-country/${selectedCountryId}`);
const { country, cities } = citiesResponse.data.data;

// Step 4: Display cities to user
console.log(`Cities in ${country.name.en}:`);
cities.forEach(city => {
    console.log(`- ${city.name.en}`);
});

// Step 5: User can now subscribe to specific cities
// Use: POST /api/company/cities/add with city_id
```

---

## Notes

1. **Multilingual Support:** All country and city names are returned in 5 languages (en, ar, de, fr, it).

2. **Performance:** This endpoint loads all cities for a country. For countries with many cities, consider pagination if needed in the future.

3. **Caching:** Consider caching the response on the frontend if the same country is selected multiple times.

4. **Empty Results:** If a country has no cities, the `cities` array will be empty `[]`.

5. **Order:** Cities are returned in the order they were created (by ID). You may want to sort them alphabetically on the frontend.

---

## Error Handling

### Common Errors

**404 - Country Not Found:**
```javascript
try {
    const response = await axios.get('/api/company/cities/by-country/999');
} catch (error) {
    if (error.response?.status === 404) {
        console.error('Country not found');
    }
}
```

**422 - Invalid Country ID:**
```javascript
try {
    const response = await axios.get('/api/company/cities/by-country/invalid');
} catch (error) {
    if (error.response?.status === 422) {
        console.error('Invalid country ID format');
    }
}
```

**401 - Unauthorized:**
```javascript
try {
    const response = await axios.get('/api/company/cities/by-country/1');
} catch (error) {
    if (error.response?.status === 401) {
        console.error('Please login again');
        // Redirect to login
    }
}
```

---

## Testing

### Using cURL
```bash
# Company API
curl -X GET "http://your-domain.com/api/company/cities/by-country/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Admin API
curl -X GET "http://your-domain.com/api/admin/cities/by-country/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Using Postman
1. Method: `GET`
2. URL: `{{base_url}}/api/company/cities/by-country/1`
3. Headers:
   - `Authorization: Bearer {{token}}`
   - `Accept: application/json`

---

## Support

For any issues or questions, please contact the backend team.
