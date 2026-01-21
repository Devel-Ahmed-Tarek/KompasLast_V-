# 📚 Hierarchical Types API Documentation

## Overview

The Types system has been updated to support **hierarchical structure** (Parent → Children).
Now types can have subtypes, allowing for more organized service categorization.

---

## 🗄️ Database Changes

### New Columns in `types` Table:

| Column | Type | Description |
|--------|------|-------------|
| `parent_id` | `bigint unsigned NULL` | Reference to parent type (NULL = parent type) |
| `order` | `integer` | Display order (default: 0) |
| `is_active` | `boolean` | Active status (default: true) |

### Structure Example:

```
🏠 Umzug (id: 1, parent_id: NULL) - Parent
   ├── 📦 Privatumzug (id: 4, parent_id: 1) - Child
   ├── 🏢 Firmenumzug (id: 5, parent_id: 1) - Child
   └── 🌍 Auslandsumzug (id: 6, parent_id: 1) - Child

🧹 Reinigung (id: 2, parent_id: NULL) - Parent
   ├── 🏠 Wohnungsreinigung (id: 7, parent_id: 2) - Child
   └── 🏢 Büroreinigung (id: 8, parent_id: 2) - Child

🔧 Reparatur (id: 3, parent_id: NULL) - Parent (no children)
```

---

## 🔌 API Endpoints

### Base URL: `/api/admin/types`

---

### 1. Get All Types (Hierarchical)

**Endpoint:** `GET /api/admin/types`

**Description:** Returns all parent types with their children nested.

**Response:**
```json
[
    {
        "id": 1,
        "name": {
            "en": "Moving",
            "de": "Umzug"
        },
        "price": "100.00",
        "parent_id": null,
        "order": 0,
        "is_active": true,
        "children": [
            {
                "id": 4,
                "name": {
                    "en": "Private Moving",
                    "de": "Privatumzug"
                },
                "price": "80.00",
                "parent_id": 1,
                "order": 0,
                "is_active": true
            },
            {
                "id": 5,
                "name": {
                    "en": "Company Moving",
                    "de": "Firmenumzug"
                },
                "price": "150.00",
                "parent_id": 1,
                "order": 1,
                "is_active": true
            }
        ]
    },
    {
        "id": 2,
        "name": {
            "en": "Cleaning",
            "de": "Reinigung"
        },
        "price": "80.00",
        "parent_id": null,
        "order": 1,
        "is_active": true,
        "children": []
    }
]
```

---

### 2. Get All Types (Flat List)

**Endpoint:** `GET /api/admin/types?flat=true`

**Description:** Returns all types in a flat list with parent info.

**Response:**
```json
[
    {
        "id": 1,
        "name": { "en": "Moving", "de": "Umzug" },
        "price": "100.00",
        "parent_id": null,
        "order": 0,
        "is_active": true,
        "parent": null
    },
    {
        "id": 4,
        "name": { "en": "Private Moving", "de": "Privatumzug" },
        "price": "80.00",
        "parent_id": 1,
        "order": 0,
        "is_active": true,
        "parent": {
            "id": 1,
            "name": { "en": "Moving", "de": "Umzug" }
        }
    }
]
```

---

### 3. Get Parent Types Only (for Dropdowns)

**Endpoint:** `GET /api/admin/types/parents`

**Description:** Returns only parent types (useful for dropdown when creating subtypes).

**Response:**
```json
[
    {
        "id": 1,
        "name": { "en": "Moving", "de": "Umzug" },
        "price": "100.00"
    },
    {
        "id": 2,
        "name": { "en": "Cleaning", "de": "Reinigung" },
        "price": "80.00"
    }
]
```

---

### 4. Get Single Type with Details

**Endpoint:** `GET /api/admin/types/{id}`

**Description:** Returns a specific type with its parent and children.

**Response:**
```json
{
    "id": 1,
    "name": { "en": "Moving", "de": "Umzug" },
    "price": "100.00",
    "parent_id": null,
    "order": 0,
    "is_active": true,
    "parent": null,
    "children": [
        {
            "id": 4,
            "name": { "en": "Private Moving", "de": "Privatumzug" },
            "price": "80.00",
            "parent_id": 1,
            "order": 0,
            "is_active": true
        }
    ]
}
```

---

### 5. Get Children of a Type

**Endpoint:** `GET /api/admin/types/{id}/children`

**Description:** Returns all subtypes of a specific parent type.

**Response:**
```json
[
    {
        "id": 4,
        "name": { "en": "Private Moving", "de": "Privatumzug" },
        "price": "80.00",
        "parent_id": 1,
        "order": 0,
        "is_active": true
    },
    {
        "id": 5,
        "name": { "en": "Company Moving", "de": "Firmenumzug" },
        "price": "150.00",
        "parent_id": 1,
        "order": 1,
        "is_active": true
    }
]
```

---

### 6. Create New Type (Parent or Child)

**Endpoint:** `POST /api/admin/types`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {token}
```

#### Create Parent Type:
```json
{
    "name": {
        "en": "Moving",
        "de": "Umzug",
        "fr": "Déménagement",
        "it": "Trasloco",
        "ar": "نقل"
    },
    "price": 100,
    "order": 0,
    "is_active": true
}
```

#### Create Child Type (Subtype):
```json
{
    "name": {
        "en": "Private Moving",
        "de": "Privatumzug"
    },
    "price": 80,
    "parent_id": 1,
    "order": 0,
    "is_active": true
}
```

**Response (201):**
```json
{
    "status": true,
    "code": 201,
    "message": "Type created successfully",
    "data": {
        "id": 4,
        "name": { "en": "Private Moving", "de": "Privatumzug" },
        "price": "80.00",
        "parent_id": 1,
        "order": 0,
        "is_active": true,
        "parent": {
            "id": 1,
            "name": { "en": "Moving", "de": "Umzug" }
        },
        "children": []
    }
}
```

---

### 7. Update Type

**Endpoint:** `PUT /api/admin/types/{id}`

**Request:**
```json
{
    "name": {
        "en": "Updated Name",
        "de": "Aktualisierter Name"
    },
    "price": 120,
    "parent_id": null,
    "order": 2,
    "is_active": true
}
```

**Note:** All fields are optional. Only send what you want to update.

---

### 8. Delete Type

**Endpoint:** `DELETE /api/admin/types/{id}`

**Response:**
```json
{
    "message": "Type and 3 subtypes deleted"
}
```

**⚠️ Warning:** Deleting a parent type will also delete all its children (cascade delete).

---

### 9. Reorder Types

**Endpoint:** `POST /api/admin/types/reorder`

**Request:**
```json
{
    "types": [
        { "id": 1, "order": 0 },
        { "id": 2, "order": 1 },
        { "id": 3, "order": 2 }
    ]
}
```

**Response:**
```json
{
    "status": true,
    "code": 200,
    "message": "Types reordered successfully"
}
```

---

### 10. Toggle Active Status

**Endpoint:** `PUT /api/admin/types/{id}/toggle-active`

**Response:**
```json
{
    "message": "Type deactivated",
    "is_active": false
}
```

---

## 🌐 Website API (Public)

### Get Types for Selection (Forms)

**Endpoint:** `GET /api/user/type/select`

#### Hierarchical Response (default):
```json
{
    "status": true,
    "code": 200,
    "message": "done",
    "data": [
        {
            "id": 1,
            "name": "Moving",
            "price": "100.00",
            "parent_id": null,
            "is_parent": true,
            "children": [
                {
                    "id": 4,
                    "name": "Private Moving",
                    "price": "80.00",
                    "parent_id": 1,
                    "is_parent": false
                }
            ]
        }
    ]
}
```

#### Flat Response:
**Endpoint:** `GET /api/user/type/select?flat=true`

```json
{
    "status": true,
    "code": 200,
    "message": "done",
    "data": [
        {
            "id": 1,
            "name": "Moving",
            "price": "100.00",
            "parent_id": null,
            "is_parent": true
        },
        {
            "id": 4,
            "name": "Private Moving",
            "price": "80.00",
            "parent_id": 1,
            "is_parent": false
        }
    ]
}
```

---

## 🏢 Company API

### Get Available Types (Not Subscribed)

**Endpoint:** `GET /api/company/git/types/dont/haveing`

**Response:** Returns types not yet subscribed by the company, with parent info.

```json
{
    "status": true,
    "code": 200,
    "data": [
        {
            "id": 4,
            "name": { "en": "Private Moving" },
            "price": "80.00",
            "parent_id": 1,
            "is_active": true,
            "parent": {
                "id": 1,
                "name": { "en": "Moving" }
            }
        }
    ]
}
```

---

## 🎨 Frontend Implementation Guide

### 1. Types List Page (Admin)

```jsx
// React Example
const TypesList = () => {
    const [types, setTypes] = useState([]);

    useEffect(() => {
        // Fetch hierarchical types
        fetch('/api/admin/types')
            .then(res => res.json())
            .then(data => setTypes(data));
    }, []);

    return (
        <div>
            {types.map(parentType => (
                <div key={parentType.id} className="parent-type">
                    <h3>{parentType.name.en}</h3>
                    <span>Price: {parentType.price} CHF</span>
                    
                    {parentType.children?.length > 0 && (
                        <div className="children-types" style={{ marginLeft: '20px' }}>
                            {parentType.children.map(child => (
                                <div key={child.id} className="child-type">
                                    └── {child.name.en} - {child.price} CHF
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
};
```

### 2. Create/Edit Type Form

```jsx
const TypeForm = ({ editingType }) => {
    const [parentTypes, setParentTypes] = useState([]);
    const [formData, setFormData] = useState({
        name: { en: '', de: '' },
        price: '',
        parent_id: null,
        order: 0,
        is_active: true
    });

    useEffect(() => {
        // Fetch parent types for dropdown
        fetch('/api/admin/types/parents')
            .then(res => res.json())
            .then(data => setParentTypes(data));
    }, []);

    return (
        <form>
            <input 
                placeholder="Name (EN)" 
                value={formData.name.en}
                onChange={e => setFormData({
                    ...formData, 
                    name: { ...formData.name, en: e.target.value }
                })}
            />
            
            <input 
                placeholder="Name (DE)" 
                value={formData.name.de}
                onChange={e => setFormData({
                    ...formData, 
                    name: { ...formData.name, de: e.target.value }
                })}
            />
            
            <input 
                type="number" 
                placeholder="Price" 
                value={formData.price}
                onChange={e => setFormData({ ...formData, price: e.target.value })}
            />
            
            {/* Parent Type Dropdown */}
            <select 
                value={formData.parent_id || ''}
                onChange={e => setFormData({ 
                    ...formData, 
                    parent_id: e.target.value || null 
                })}
            >
                <option value="">-- No Parent (Main Type) --</option>
                {parentTypes.map(type => (
                    <option key={type.id} value={type.id}>
                        {type.name.en}
                    </option>
                ))}
            </select>
            
            <input 
                type="number" 
                placeholder="Order" 
                value={formData.order}
                onChange={e => setFormData({ ...formData, order: e.target.value })}
            />
            
            <label>
                <input 
                    type="checkbox" 
                    checked={formData.is_active}
                    onChange={e => setFormData({ ...formData, is_active: e.target.checked })}
                />
                Active
            </label>
            
            <button type="submit">Save</button>
        </form>
    );
};
```

### 3. Type Selection Dropdown (Website Forms)

```jsx
const TypeSelector = ({ value, onChange }) => {
    const [types, setTypes] = useState([]);

    useEffect(() => {
        fetch('/api/user/type/select')
            .then(res => res.json())
            .then(data => setTypes(data.data));
    }, []);

    return (
        <select value={value} onChange={e => onChange(e.target.value)}>
            <option value="">Select Service Type</option>
            {types.map(parentType => (
                <optgroup key={parentType.id} label={parentType.name}>
                    {/* Allow selecting parent */}
                    <option value={parentType.id}>
                        {parentType.name} (General)
                    </option>
                    
                    {/* Children options */}
                    {parentType.children?.map(child => (
                        <option key={child.id} value={child.id}>
                            &nbsp;&nbsp;└── {child.name}
                        </option>
                    ))}
                </optgroup>
            ))}
        </select>
    );
};
```

---

## ⚠️ Important Notes

1. **Offers & Company Subscriptions:** Both can link to either parent types OR subtypes.

2. **Cascade Delete:** Deleting a parent type will delete all its subtypes automatically.

3. **Active Status:** Inactive types (`is_active: false`) won't appear in website selection.

4. **Order Field:** Use for custom sorting. Lower numbers appear first.

5. **Backward Compatibility:** Existing types without `parent_id` are treated as parent types.

---

## 📅 Migration Required

Run this command after deployment:

```bash
php artisan migrate
```

---

## 📞 Contact

For questions, contact the backend team.

**Last Updated:** January 21, 2026
