/**
 * Constantes centralizadas para módulos del sistema
 * Evita repetición de código y facilita mantenimiento
 */

// Estados comunes
export const STATUS = {
    ACTIVE: "active",
    INACTIVE: "inactive",
    PENDING: "pending",
    CANCELED: "canceled",
    SENT: "sent",
    FAILED: "failed",
    MAINTENANCE: "maintenance",
    RETIRED: "retired"
};

// Variantes de badges por estado
export const STATUS_BADGE_VARIANTS = {
    [STATUS.ACTIVE]: "bg-label-success",
    [STATUS.INACTIVE]: "bg-label-danger",
    [STATUS.PENDING]: "bg-label-warning",
    [STATUS.CANCELED]: "bg-label-danger",
    [STATUS.SENT]: "bg-label-success",
    [STATUS.FAILED]: "bg-label-danger",
    [STATUS.MAINTENANCE]: "bg-label-warning",
    [STATUS.RETIRED]: "bg-label-danger"
};

// Opciones de filtrado comunes
export const FILTER_BY_OPTIONS = {
    ALL: {code: "all", label: "Todos"},
    INTERNAL_CODE: {code: "internal_code", label: "Código interno"},
    NAME: {code: "name", label: "Nombre"},
    DESCRIPTION: {code: "description", label: "Descripción"},
    DOCUMENT_NUMBER: {code: "document_number", label: "Número de documento"},
    EMAIL: {code: "email", label: "Correo electrónico"}
};

// Clases CSS comunes
export const CSS_CLASSES = {
    TITLE: "fw-bold colon-at-end fs-6",
    SELECT2: "bg-white",
    BADGE_BASE: ["badge", "fw-semibold", "text-capitalize"],
    BUTTON_PRIMARY: "btn btn-primary waves-effect",
    BUTTON_SUCCESS: "btn btn-success waves-effect",
    BUTTON_WARNING: "btn btn-warning waves-effect",
    BUTTON_DANGER: "btn btn-danger waves-effect",
    BUTTON_INFO: "btn btn-info-1 waves-effect",
    BUTTON_SECONDARY: "btn btn-secondary waves-effect"
};

// Textos comunes
export const TEXT = {
    SEARCH: "Buscar",
    ADD: "Agregar",
    EDIT: "Editar",
    DELETE: "Eliminar",
    SAVE: "Guardar",
    CANCEL: "Cancelar",
    CLOSE: "Cerrar",
    ACTIONS: "Acciones",
    DETAIL: "Detalle",
    FILTER_BY: "Filtrar por",
    SEARCH_PLACEHOLDER: "Buscar...",
    WITHOUT_DATA: "Sin registros",
    LOADING: "Cargando...",
    YES: "Sí",
    NO: "No"
};

// Configuración de modales
export const MODAL_CONFIG = {
    BACKDROP: "static",
    KEYBOARD: false,
    CENTERED: true
};

// Configuración de paginación
export const PAGINATION = {
    PER_PAGE_OPTIONS: [10, 25, 50, 100],
    DEFAULT_PER_PAGE: 10
};

// Tipos de documentos
export const DOCUMENT_TYPES = {
    DNI: "dni",
    RUC: "ruc",
    CE: "ce",
    PASSPORT: "passport"
};

// Géneros
export const GENDERS = {
    MALE: {code: "male", label: "Masculino"},
    FEMALE: {code: "female", label: "Femenino"},
    OTHER: {code: "other", label: "Otro"}
};

// Tipos de gestión de activos
export const ASSET_MANAGEMENT_TYPES = {
    STOCK: "stock",
    UNIT: "unit"
};

// Rutas de entidades comunes
export const ENTITY_ROUTES = {
    DASHBOARD: "dashboard",
    USERS: "users",
    COMPANIES: "companies",
    BRANCHES: "branches",
    CUSTOMERS: "customers",
    SALES: "sales",
    ASSETS: "assets",
    ASSETS_MANAGEMENT: "assets_management",
    TRACKING_ATTENDANCES: "tracking_attendances",
    TRACKING_CUSTOMERS: "tracking_customers",
    TRACKING_SUBSCRIPTIONS: "tracking_subscriptions",
    TRACKING_NOTIFICATIONS: "tracking_notifications",
    STOCKS_MANAGEMENT: "stocks_management",
    REPORTS: "reports"
};

// Configuración de validación común
export const VALIDATION = {
    REQUIRED: "required",
    EMAIL: "email",
    URL: "url",
    NUMBER: "number",
    MIN_LENGTH: "minLength",
    MAX_LENGTH: "maxLength"
};

// Mensajes de confirmación comunes
export const CONFIRM_MESSAGES = {
    DELETE: "¿Está seguro de eliminar este registro?",
    CANCEL: "¿Está seguro de cancelar esta operación?",
    UNASSIGN: "¿Desea quitar este elemento?",
    ASSIGN: "¿Desea agregar este elemento?"
};

