# Project Setup Guide

## Cloning the Repository

1. Open a terminal or command prompt.
2. Navigate to the directory where you want to clone the project.
   ```sh
   cd /path/to/your/directory
   ```
3. Clone the repository using Git:
   ```sh
   git clone {{url}} chat_session_api
   ```
4. change directory into the project directory:
   ```sh
   cd chat_session_api
   ```

## Tech Stacks
- Laravel 10

## Environment Configuration 

Copy the `.env.example` file to create a new `.env` file:
   ```sh
   cp .env.example .env
   composer install
   ```

   And replace the database credentials here.
   ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=
    DB_USERNAME=
    DB_PASSWORD=
   ```


## Database Migrating and Running the Application

   ```sh
    php artisan migrate:fresh --seed
    php artisan module:seed Chat

    php artisan serve --port=8000
   ```

## Code Implementation

### Migration
- use uuid and unique in session_uuid
- control current_state values with ChatSessionStates ENUM (in database, just String becuase the values may be updated later. Now, controlled by Validation)
- session_uuid and current_state columns are indexed because those may be used in filters many times 

### ChatSession Eloquent Model
The outstanding points are
- scopefindBySessionUuid method
- booted method that implements saving session_uuid.
- implementation of updateState function as mentioned

### Controller -> Service -> Repository
- The Structure of code implementation is Controller -> Service -> Repository
- In validation, created seperate request files, for **state value validation**, used **ChatSessionStates ENUM** for consistency and maintenance.
- In responses, ChatSessionResource is used for proper response data management.
- In Service, an interface is created, then real implementation implements it for dependency inversion.
- In Repository, implemented the functions, filters, pagination related with DB, for updating, used lockForUpdate for safety in concurrent updates.

### Exception Handling
In app\Exceptions\Handler.php, global respoonses are implemented for errors.
Therefore, for example, **No query result error** are catched here and response consistently.

```
 if ($request->is('api/*')) {
   if ($e instanceof HttpException) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], $e->getStatusCode());
    }
 }
```

### API Payloads and Responses

url - http://127.0.0.1:8000/api/v1

#### Save a new Chat Session
`{{url}}/chat-sessions`

Payload
```
{
    "meta": {
        "step": 1
    }
}
```

Response (status code - 201)
```
{
    "status": true,
    "data": {
        "chat_session": {
            "id": 1,
            "session_uuid": "b148f718-9403-4a1b-ae61-627ee0d720fc",
            "current_state": "started",
            "meta": {
                "step": 1
            },
            "created_at": "2025-12-19T14:06:11.000000Z",
            "updated_at": "2025-12-19T14:06:11.000000Z"
        }
    },
    "message": "Successfully Saved"
}
```

#### GET Chat Sessions with Pagination
```
{{url}}/chat-sessions?states=started,completed&page=1&per_page=10
```
Response (status code - 200)
```
{
    "status": true,
    "data": {
        "chat_sessions": {
            "current_page": 1,
            "data": [
                {
                    "id": 1,
                    "session_uuid": "b148f718-9403-4a1b-ae61-627ee0d720fc",
                    "current_state": "collecting_info",
                    "meta": {
                        "step": 1
                    },
                    "created_at": "2025-12-19T14:06:11.000000Z",
                    "updated_at": "2025-12-19T14:09:23.000000Z"
                }
            ],
            "first_page_url": "http://127.0.0.1:8000/api/v1/chat-sessions?page=1",
            "from": 1,
            "last_page": 1,
            "last_page_url": "http://127.0.0.1:8000/api/v1/chat-sessions?page=1",
            "links": [
                {
                    "url": null,
                    "label": "&laquo; Previous",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/chat-sessions?page=1",
                    "label": "1",
                    "active": true
                },
                {
                    "url": null,
                    "label": "Next &raquo;",
                    "active": false
                }
            ],
            "next_page_url": null,
            "path": "http://127.0.0.1:8000/api/v1/chat-sessions",
            "per_page": 15,
            "prev_page_url": null,
            "to": 1,
            "total": 1
        }
    },
    "message": ""
}
```
#### GET a Chat Session by session_uuid
`{{url}}/chat-sessions/b148f718-9403-4a1b-ae61-627ee0d720fc`

Response (status code - 200)
```
{
    "status": true,
    "data": {
        "chat_session": {
            "id": 1,
            "session_uuid": "b148f718-9403-4a1b-ae61-627ee0d720fc",
            "current_state": "started",
            "meta": {
                "step": 1
            },
            "created_at": "2025-12-19T14:06:11.000000Z",
            "updated_at": "2025-12-19T14:08:22.000000Z"
        }
    },
    "message": ""
}
```


#### Update Chat Session
`{{url}}/chat-sessions/b148f718-9403-4a1b-ae61-627ee0d720fc`

Payload
```
{
    "meta": {
        "step": 1
    }
}
```

Response (status code - 200)
```
{
    "status": true,
    "data": {
        "chat_session": {
            "id": 1,
            "session_uuid": "b148f718-9403-4a1b-ae61-627ee0d720fc",
            "current_state": "collecting_info",
            "meta": {
                "step": 2
            },
            "created_at": "2025-12-19T14:06:11.000000Z",
            "updated_at": "2025-12-19T16:11:24.000000Z"
        }
    },
    "message": "Successfully Updated"
}
```

#### Delete Chat Session

`{{url}}/chat-sessions-bulk-delete`

Response (status code - 204, No Content)

#### Bulk Delete Chat Sessions

`{{url}}/chat-sessions-bulk-delete`

Payload

```
{
    "session_uuids": [
        "b148f718-9403-4a1b-ae61-627ee0d720fc"
    ]
}
```

Response (status code - 204, No Content)