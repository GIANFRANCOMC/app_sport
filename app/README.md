# Backend Laravel - Documentación

## Estructura Optimizada

Este backend ha sido refactorizado para facilitar la creación de nuevos módulos y reducir la repetición de código, siguiendo principios SOLID y mejores prácticas de Laravel.

## Arquitectura

### Patrón de Arquitectura

El backend sigue una arquitectura en capas:

```
Controller → Service → Repository → Model
     ↓         ↓           ↓
  ConfigService (para parámetros de inicialización)
```

### Componentes Principales

#### 1. **Controllers** (`app/Http/Controllers/System/`)

Los controladores son delgados y solo manejan:
- Validación de requests
- Llamadas a servicios
- Respuestas HTTP

**Base Controller:**
- `BaseController` - Clase base con funcionalidades comunes

**Trait:**
- `HandlesApiResponses` - Manejo consistente de respuestas API con traducciones

**Ejemplo:**
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\System\Concerns\HandlesApiResponses;
use App\Services\System\Catalogs\Categories\{CategoryConfigService, CategoryService};
use Illuminate\Http\{JsonResponse, Request};

class CategoryController extends Controller {

    use HandlesApiResponses;

    private const TRANSLATION_NAMESPACE = "System.Catalogs.category";

    public function initParams(Request $request) {
        $userAuth = Auth::user();
        $page     = $request->input("page", "");
        return CategoryConfigService::getInitParams($userAuth->company_id, $page);
    }

    public function list(Request $request) {
        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);
        return CategoryService::getPaginatedList($userAuth->company_id, $filters, $perPage);
    }

    public function store(StoreCategoryRequest $request): JsonResponse {
        try {
            $userAuth = Auth::user();
            $data     = $this->prepareCategoryData($request, $userAuth);
            $category = CategoryService::create($data, $userAuth->id);

            if(!Utilities::isDefined($category)) {
                return $this->errorResponse("create_failed");
            }

            CategoryConfigService::clearAllCache($userAuth->company_id);
            return $this->createdResponse($category, "created", "category");

        } catch(Exception $e) {
            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);
        }
    }

    protected function getTranslationNamespace(): string {
        return self::TRANSLATION_NAMESPACE;
    }
}
```

#### 2. **Services** (`app/Services/System/`)

Los servicios contienen la lógica de negocio:

**Base Service:**
- `BaseService` - Clase base con funcionalidades comunes

**Tipos de Servicios:**
- `*Service` - Lógica de negocio (create, update, delete, list)
- `*ConfigService` - Parámetros de inicialización y caché

**Ejemplo:**
```php
<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use App\Services\System\Base\BaseService;
use App\Models\System\Catalogs\Category;

class CategoryService extends BaseService {

    private const TRANSLATION_NAMESPACE = "System.Catalogs.category";
    private const ALLOWED_FIELDS = ["internal_code", "name", "description", "status"];

    protected static function getTranslationNamespace(): string {
        return self::TRANSLATION_NAMESPACE;
    }

    public static function create(array $data, int $userId): ?Category {
        return static::transaction(function() use($data, $userId) {
            $categoryData = static::prepareDataForCreate(
                $data,
                $data["company_id"],
                $userId,
                self::ALLOWED_FIELDS
            );

            return Category::create($categoryData);
        });
    }

    public static function getPaginatedList(int $companyId, array $filters, int $perPage) {
        $repository = new CategoryRepository();
        return $repository->getPaginatedList($companyId, $filters, $perPage);
    }
}
```

#### 3. **Repositories** (`app/Repositories/System/`)

Los repositorios manejan el acceso a datos:

**Base Repository:**
- `BaseRepository` - Clase base con queries comunes

**Ejemplo:**
```php
<?php

declare(strict_types=1);

namespace App\Repositories\System\Catalogs;

use App\Repositories\System\Base\BaseRepository;
use App\Models\System\Catalogs\Category;

class CategoryRepository extends BaseRepository {

    protected static function getModelClass(): string {
        return Category::class;
    }

    protected static function getSearchableFields(): array {
        return ["internal_code", "name", "description"];
    }
}
```

#### 4. **Config Services** (`app/Services/System/*/`)

Manejan parámetros de inicialización y caché:

**Base Config Service:**
- `BaseConfigService` - Clase base con manejo de caché

**Ejemplo:**
```php
<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use App\Services\System\Base\BaseConfigService;
use App\Models\System\Catalogs\Category;

class CategoryConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {
        return "category_config";
    }

    public static function getInitParams(int $companyId, string $page = ""): stdClass {
        $cacheKey = self::buildCacheKey($companyId, $page);

        return self::remember($cacheKey, function() use($companyId, $page) {
            $config = new stdClass();

            if($page === "main") {
                $config->categories = new stdClass();
                $config->categories->records = Category::getAll("default", $companyId);
            }

            return self::createInitParams($config);
        });
    }
}
```

## Helpers del Sistema

### ApiResponse (`app/Helpers/System/ApiResponse.php`)

Formatea respuestas API consistentes:

```php
// Success
ApiResponse::success($data, "Mensaje de éxito");

// Error
ApiResponse::error("Mensaje de error", 500);

// Created
ApiResponse::created($resource, "Creado exitosamente", "category");

// Updated
ApiResponse::updated($resource, "Actualizado exitosamente", "category");

// Not Found
ApiResponse::notFound("Recurso no encontrado");

// Validation Error
ApiResponse::validationError($errors, "Error de validación");
```

### QueryHelper (`app/Helpers/System/QueryHelper.php`)

Operaciones comunes de queries:

```php
// Paginación
QueryHelper::paginate($query, 15, 1000);

// Búsqueda
QueryHelper::applySearch($query, "name", "valor");

// Búsqueda múltiple
QueryHelper::applyMultiFieldSearch($query, ["name", "code"], "valor");

// Filtro de estado
QueryHelper::applyStatusFilter($query, "active");

// Rango de fechas
QueryHelper::applyDateRangeFilter($query, "created_at", "2024-01-01", "2024-12-31");

// Ordenamiento
QueryHelper::applyOrdering($query, "name", "ASC");

// Filtro de compañía
QueryHelper::applyCompanyFilter($query, $companyId);
```

### DataTransformer (`app/Helpers/System/DataTransformer.php`)

Transformación de datos:

```php
// Transformar modelo
DataTransformer::transformModel($model, ["id", "name", "status"]);

// Transformar colección
DataTransformer::transformCollection($collection, function($item) {
    return ["id" => $item->id, "name" => $item->name];
});

// Transformar paginación
DataTransformer::transformPaginated($paginator, $callback);

// Agregar status formateado
DataTransformer::addFormattedStatus($model);

// Agregar fechas formateadas
DataTransformer::addFormattedDates($model, ["created_at", "updated_at"]);
```

### Utilities (`app/Helpers/System/Utilities.php`)

Utilidades generales:

```php
// Verificar si está definido
Utilities::isDefined($value);

// Búsqueda de palabras
Utilities::getWordSearch("texto", "like"); // "%texto%"

// Generar código
Utilities::generateCode(12);

// Validar formato de fecha
Utilities::isValidDateFormat("2024-01-01", "Y-m-d");
```

## Estructura de Respuestas API

### Formato Estándar

**Success:**
```json
{
    "bool": true,
    "msg": "Mensaje de éxito",
    "data": { ... }
}
```

**Error:**
```json
{
    "bool": false,
    "msg": "Mensaje de error",
    "errors": { ... } // Opcional para errores de validación
}
```

**Created/Updated:**
```json
{
    "bool": true,
    "msg": "Creado exitosamente",
    "category": { ... } // Nombre del recurso
}
```

## Cómo Crear un Nuevo Módulo

### Paso 1: Crear el Modelo

```php
<?php

namespace App\Models\System\Catalogs;

use Illuminate\Database\Eloquent\Model;

class MyEntity extends Model {
    protected $table = "my_entities";
    // ...
}
```

### Paso 2: Crear el Repository

```php
<?php

namespace App\Repositories\System\Catalogs;

use App\Repositories\System\Base\BaseRepository;
use App\Models\System\Catalogs\MyEntity;

class MyEntityRepository extends BaseRepository {

    protected static function getModelClass(): string {
        return MyEntity::class;
    }

    protected static function getSearchableFields(): array {
        return ["name", "code"];
    }
}
```

### Paso 3: Crear el Service

```php
<?php

namespace App\Services\System\Catalogs;

use App\Services\System\Base\BaseService;
use App\Repositories\System\Catalogs\MyEntityRepository;
use App\Models\System\Catalogs\MyEntity;

class MyEntityService extends BaseService {

    private const TRANSLATION_NAMESPACE = "System.Catalogs.my_entity";
    private const ALLOWED_FIELDS = ["name", "code", "status"];

    protected static function getTranslationNamespace(): string {
        return self::TRANSLATION_NAMESPACE;
    }

    public static function create(array $data, int $userId): ?MyEntity {
        return static::transaction(function() use($data, $userId) {
            $entityData = static::prepareDataForCreate(
                $data,
                $data["company_id"],
                $userId,
                self::ALLOWED_FIELDS
            );

            return MyEntity::create($entityData);
        });
    }

    public static function getPaginatedList(int $companyId, array $filters, int $perPage) {
        $repository = new MyEntityRepository();
        return $repository->getPaginatedList($companyId, $filters, $perPage);
    }
}
```

### Paso 4: Crear el ConfigService

```php
<?php

namespace App\Services\System\Catalogs;

use App\Services\System\Base\BaseConfigService;
use App\Models\System\Catalogs\MyEntity;

class MyEntityConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {
        return "my_entity_config";
    }

    public static function getInitParams(int $companyId, string $page = ""): stdClass {
        $cacheKey = self::buildCacheKey($companyId, $page);

        return self::remember($cacheKey, function() use($companyId, $page) {
            $config = new stdClass();

            if($page === "main") {
                $config->myEntities = new stdClass();
                $config->myEntities->records = MyEntity::getAll("default", $companyId);
            }

            return self::createInitParams($config);
        });
    }
}
```

### Paso 5: Crear el Controller

```php
<?php

namespace App\Http\Controllers\System\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\System\Concerns\HandlesApiResponses;
use App\Services\System\Catalogs\{MyEntityConfigService, MyEntityService};
use Illuminate\Http\{JsonResponse, Request};

class MyEntityController extends Controller {

    use HandlesApiResponses;

    private const TRANSLATION_NAMESPACE = "System.Catalogs.my_entity";

    public function initParams(Request $request) {
        $userAuth = Auth::user();
        $page     = $request->input("page", "");
        return MyEntityConfigService::getInitParams($userAuth->company_id, $page);
    }

    public function list(Request $request) {
        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);
        return MyEntityService::getPaginatedList($userAuth->company_id, $filters, $perPage);
    }

    public function store(StoreMyEntityRequest $request): JsonResponse {
        try {
            $userAuth = Auth::user();
            $data     = $request->validated();
            $data["company_id"] = $userAuth->company_id;
            $entity   = MyEntityService::create($data, $userAuth->id);

            if(!Utilities::isDefined($entity)) {
                return $this->errorResponse("create_failed");
            }

            MyEntityConfigService::clearAllCache($userAuth->company_id);
            return $this->createdResponse($entity, "created", "my_entity");

        } catch(Exception $e) {
            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);
        }
    }

    protected function getTranslationNamespace(): string {
        return self::TRANSLATION_NAMESPACE;
    }
}
```

## Buenas Prácticas

1. **Siempre usar `declare(strict_types=1)`** en todos los archivos PHP
2. **Usar type hints** en todos los métodos
3. **Separar responsabilidades**: Controllers → Services → Repositories
4. **Usar transacciones** para operaciones que modifican múltiples tablas
5. **Cachear parámetros de inicialización** en ConfigServices
6. **Validar datos** en FormRequests
7. **Usar traducciones** para todos los mensajes
8. **Manejar excepciones** apropiadamente
9. **Documentar código** con PHPDoc
10. **Seguir PSR-12** para estilo de código

## Convenciones de Nombres

- **Controllers**: `*Controller` (ej: `CategoryController`)
- **Services**: `*Service` (ej: `CategoryService`)
- **ConfigServices**: `*ConfigService` (ej: `CategoryConfigService`)
- **Repositories**: `*Repository` (ej: `CategoryRepository`)
- **Models**: Singular (ej: `Category`)
- **Requests**: `Store*Request`, `Update*Request` (ej: `StoreCategoryRequest`)

## Estructura de Directorios

```
app/
├── Http/
│   └── Controllers/
│       └── System/
│           ├── Base/
│           │   └── BaseController.php
│           ├── Concerns/
│           │   └── HandlesApiResponses.php
│           └── [Módulos]/
│
├── Services/
│   └── System/
│       ├── Base/
│       │   ├── BaseService.php
│       │   └── BaseConfigService.php
│       └── [Módulos]/
│
├── Repositories/
│   └── System/
│       ├── Base/
│       │   └── BaseRepository.php
│       └── [Módulos]/
│
└── Helpers/
    └── System/
        ├── ApiResponse.php
        ├── QueryHelper.php
        ├── DataTransformer.php
        ├── TranslationHelper.php
        └── Utilities.php
```

## Notas Importantes

- Todas las respuestas API siguen el formato estándar para compatibilidad con el frontend
- El caché se limpia automáticamente después de crear/actualizar recursos
- Las traducciones se manejan centralmente con fallback
- Los repositorios implementan el patrón Repository para centralizar queries
- Los servicios manejan la lógica de negocio y transacciones
- Los controladores son delgados y solo coordinan

