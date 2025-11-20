<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <div v-if="isDefined({ value: lists }) && isDefined({ value: lists[entity] })">
        <!-- Filters Section -->
        <section class="filters-section mb-3 mb-md-4" aria-label="Filters">
            <div class="row align-items-end g-3">
                <InputSlot
                    hasDiv
                    :title="TEXTS.filters.filterBy"
                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title] : []"
                    xl="3"
                    lg="4">
                    <template v-slot:input>
                        <v-select
                            v-model="filterByValue"
                            :options="filterByOptions"
                            :class="config?.forms?.classes?.select2 || ''"
                            :clearable="false"
                            :searchable="false"
                            :aria-label="TEXTS.filters.filterBy"
                            :disabled="entityList?.extras?.loading"/>
                    </template>
                </InputSlot>
                <InputText
                    v-model="filterWordValue"
                    @enterKeyPressed="handleSearch"
                    hasDiv
                    :title="TEXTS.filters.search"
                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title] : []"
                    :placeholder="searchPlaceholder"
                    :disabled="entityList?.extras?.loading"
                    :aria-label="TEXTS.filters.search"
                    xl="4"
                    lg="4"/>
                <InputSlot
                    hasDiv
                    :isInputGroup="false"
                    :divInputClass="['d-flex flex-wrap justify-content-start gap-2 gap-md-3']"
                    xl="5"
                    lg="4">
                    <template v-slot:input>
                        <button
                            type="button"
                            class="btn btn-info-1 waves-effect"
                            @click="handleSearch"
                            :disabled="entityList?.extras?.loading"
                            :aria-label="TEXTS.actions.search"
                            :aria-busy="entityList?.extras?.loading">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <span class="ms-2" v-text="TEXTS.actions.search"></span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary waves-effect"
                            @click="openModal()"
                            :disabled="entityList?.extras?.loading"
                            :aria-label="TEXTS.actions.add">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span class="ms-2" v-text="TEXTS.actions.add"></span>
                        </button>
                    </template>
                </InputSlot>
            </div>
        </section>

        <!-- List Section -->
        <section
            v-if="entityList"
            class="list-section mb-4"
            :aria-label="`${MODULE_CONFIG.pageTitle} list`">
            <div
                v-if="entityList && entityList.extras && entityList.extras.loading"
                class="py-5 text-center"
                role="status"
                aria-live="polite"
                :aria-label="`Loading ${MODULE_CONFIG.pageTitle}...`">
                <Loader/>
                <span class="visually-hidden-custom" v-text="TEXTS.loading"></span>
            </div>
            <template v-else>
                <div
                    v-if="entityList && entityList.records && entityList.records.total > 0"
                    class="row g-3 g-lg-4"
                    role="list"
                    :aria-label="`${entityList.records.total || 0} ${TEXTS.list.totalItems}`">
                    <div
                        v-for="record in (entityList.records && entityList.records.data ? entityList.records.data : [])"
                        :key="record.id"
                        class="col-12 col-md-6 col-xl-4"
                        role="listitem">
                        <article
                            class="card card-list-custom border-0 shadow-sm h-100"
                            :aria-label="`${TEXTS.card.branch} ${record.name}`">
                            <div class="card-body d-flex flex-column gap-3">
                                <!-- Header: Code and Status -->
                                <header class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                                    <div class="d-flex flex-column">
                                        <span
                                            class="text-uppercase text-muted small fw-semibold"
                                            aria-label="Internal code"
                                            v-text="TEXTS.card.internalCode"></span>
                                        <span
                                            class="fs-5 fw-bold text-primary"
                                            v-text="record.internal_code || TEXTS.card.notDefined"
                                            :aria-label="`${TEXTS.card.internalCode}: ${record.internal_code || TEXTS.card.notDefined}`"></span>
                                    </div>
                                    <span
                                        :class="getStatusBadgeClasses(record.status)"
                                        v-text="record.formatted_status"
                                        :aria-label="`${TEXTS.card.status}: ${record.formatted_status}`"
                                        role="status"></span>
                                </header>

                                <!-- Title -->
                                <div class="d-flex flex-column gap-1">
                                    <h3
                                        class="h5 fw-semibold mb-0 text-dark text-truncate"
                                        :title="record.name"
                                        v-text="record.name"
                                        style="min-width: 0;"></h3>
                                </div>

                                <!-- Details -->
                                <div class="d-flex flex-column gap-2" role="group" :aria-label="TEXTS.card.details">
                                    <!-- Address -->
                                    <div
                                        class="d-flex align-items-center gap-2"
                                        :class="isDefined(record.address) ? 'text-muted' : ''">
                                        <i
                                            class="fa fa-map-marker-alt text-danger"
                                            style="min-width: 20px; flex-shrink: 0;"
                                            aria-hidden="true"></i>
                                        <span
                                            v-if="isDefined(record.address)"
                                            class="text-truncate flex-grow-1 small"
                                            :title="record.address"
                                            v-text="record.address"
                                            style="min-width: 0;"
                                            :aria-label="`${TEXTS.card.address}: ${record.address}`"></span>
                                        <span
                                            v-else
                                            class="text-muted small"
                                            :aria-label="TEXTS.card.noAddress"
                                            v-text="TEXTS.card.noAddress"></span>
                                    </div>

                                    <!-- Reference -->
                                    <div
                                        v-if="isDefined(record.reference)"
                                        class="d-flex align-items-center gap-2 text-muted small">
                                        <i
                                            class="fa fa-comment-dots text-info"
                                            style="min-width: 20px; flex-shrink: 0;"
                                            aria-hidden="true"></i>
                                        <span
                                            class="text-truncate flex-grow-1"
                                            :title="record.reference"
                                            v-text="record.reference"
                                            style="min-width: 0;"
                                            :aria-label="`${TEXTS.card.reference}: ${record.reference}`"></span>
                                    </div>

                                    <!-- Telephone -->
                                    <div
                                        class="d-flex align-items-center gap-2"
                                        :class="isDefined(record.telephone) ? 'text-muted' : ''">
                                        <i
                                            class="fa fa-phone text-primary"
                                            style="min-width: 20px; flex-shrink: 0;"
                                            aria-hidden="true"></i>
                                        <span
                                            v-if="isDefined(record.telephone)"
                                            class="text-truncate flex-grow-1 small"
                                            :title="record.telephone"
                                            v-text="record.telephone"
                                            style="min-width: 0;"
                                            :aria-label="`${TEXTS.card.telephone}: ${record.telephone}`"></span>
                                        <span
                                            v-else
                                            class="text-muted small"
                                            :aria-label="TEXTS.card.noTelephone"
                                            v-text="TEXTS.card.noTelephone"></span>
                                    </div>

                                    <!-- Email -->
                                    <div
                                        class="d-flex align-items-center gap-2"
                                        :class="isDefined(record.email) ? 'text-muted' : ''">
                                        <i
                                            class="fa fa-envelope text-primary"
                                            style="min-width: 20px; flex-shrink: 0;"
                                            aria-hidden="true"></i>
                                        <span
                                            v-if="isDefined(record.email)"
                                            class="text-truncate flex-grow-1 small"
                                            :title="record.email"
                                            v-text="record.email"
                                            style="min-width: 0;"
                                            :aria-label="`${TEXTS.card.email}: ${record.email}`"></span>
                                        <span
                                            v-else
                                            class="text-muted small"
                                            :aria-label="TEXTS.card.noEmail"
                                            v-text="TEXTS.card.noEmail"></span>
                                    </div>

                                    <!-- Capacity -->
                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i
                                            class="fa fa-users text-success"
                                            style="min-width: 20px; flex-shrink: 0;"
                                            aria-hidden="true"></i>
                                        <span
                                            :class="['text-truncate flex-grow-1', hasValidCapacity(record.capacity) ? '' : 'small']"
                                            v-text="formatCapacity(record.capacity)"
                                            style="min-width: 0;"
                                            :aria-label="`${TEXTS.card.capacity}: ${formatCapacity(record.capacity)}`"></span>
                                    </div>
                                </div>
                            </div>
                            <!-- Footer: Actions -->
                            <footer class="card-footer bg-transparent border-0 pt-0 mt-auto">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                                    <a
                                        v-if="isDefined(record.map_url)"
                                        :href="record.map_url"
                                        class="btn btn-sm btn-outline-success waves-effect"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        :aria-label="`${TEXTS.actions.viewMap} ${record.name}`">
                                        <i class="fa fa-map-location-dot" aria-hidden="true"></i>
                                        <span class="ms-2 d-none d-sm-inline" v-text="TEXTS.actions.viewMap"></span>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning waves-effect"
                                        :class="isDefined(record.map_url) ? '' : 'ms-auto'"
                                        @click="openModal(record)"
                                        :aria-label="`${TEXTS.actions.edit} ${record.name}`">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        <span class="ms-2 d-none d-sm-inline" v-text="TEXTS.actions.edit"></span>
                                    </button>
                                </div>
                            </footer>
                        </article>
                    </div>
                </div>
                <div
                    v-else
                    class="text-center"
                    role="status"
                    aria-live="polite">
                    <WithoutData type="image"/>
                    <p class="visually-hidden-custom" v-text="TEXTS.list.noData"></p>
                </div>
            </template>
        </section>

        <!-- Pagination -->
        <nav v-if="entityList && entityList.records && entityList.records.last_page > 1 && entityList.records.total > 0 && entityList.extras && !entityList.extras.loading" class="d-flex justify-content-center" aria-label="Pagination">
            <Paginator :links="(entityList.records && entityList.records.links ? entityList.records.links : [])" @clickPage="listEntity"/>
        </nav>

        <!-- Modal: Create/Update -->
        <div
            v-if="forms[entity]?.createUpdate?.extras?.modals?.default?.id"
            class="modal fade"
            :id="forms[entity].createUpdate.extras.modals.default.id"
            data-bs-backdrop="static"
            tabindex="-1"
            :aria-labelledby="`${forms[entity].createUpdate.extras.modals.default.id}-title`"
            aria-hidden="true"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-uppercase fw-bold" :id="`${forms[entity].createUpdate.extras.modals.default.id}-title`" v-text="modalTitles[isUpdate ? 'update' : 'store']"></h5>
                        <button type="button" class="a-close-modal" data-bs-dismiss="modal" :aria-label="TEXTS.modal.close" :aria-describedby="`${forms[entity].createUpdate.extras.modals.default.id}-title`">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveEntity" :aria-label="modalTitles[isUpdate ? 'update' : 'store']">
                            <div class="row g-3">
                                <InputText
                                    v-model="forms[entity].createUpdate.data.internal_code"
                                    hasDiv
                                    :title="TEXTS.form.internalCode"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    isRequired
                                    maxlength="50"
                                    :placeholder="TEXTS.form.placeholders.internalCode"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.internal_code"
                                    :aria-label="TEXTS.form.internalCode"
                                    xl="4"
                                    lg="4"/>
                                <InputText
                                    v-model="forms[entity].createUpdate.data.name"
                                    hasDiv
                                    :title="TEXTS.form.name"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    isRequired
                                    maxlength="100"
                                    :placeholder="TEXTS.form.placeholders.name"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.name"
                                    :aria-label="TEXTS.form.name"
                                    xl="8"
                                    lg="8"/>
                                <InputText
                                    v-model="forms[entity].createUpdate.data.address"
                                    hasDiv
                                    :title="TEXTS.form.address"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    maxlength="100"
                                    :placeholder="TEXTS.form.placeholders.address"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.address"
                                    :aria-label="TEXTS.form.address"
                                    xl="12"
                                    lg="12"/>
                                <InputText
                                    v-model="forms[entity].createUpdate.data.reference"
                                    hasDiv
                                    :title="TEXTS.form.reference"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    maxlength="150"
                                    :placeholder="TEXTS.form.placeholders.reference"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.reference"
                                    :aria-label="TEXTS.form.reference"
                                    xl="12"
                                    lg="12"/>
                                <InputText
                                    v-model="forms[entity].createUpdate.data.telephone"
                                    hasDiv
                                    :title="TEXTS.form.telephone"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    maxlength="25"
                                    :placeholder="TEXTS.form.placeholders.telephone"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.telephone"
                                    :aria-label="TEXTS.form.telephone"
                                    xl="6"
                                    lg="6"/>
                                <InputText
                                    v-model="forms[entity].createUpdate.data.email"
                                    hasDiv
                                    :title="TEXTS.form.email"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    maxlength="120"
                                    :placeholder="TEXTS.form.placeholders.email"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.email"
                                    :aria-label="TEXTS.form.email"
                                    xl="6"
                                    lg="6"/>
                                <InputNumber
                                    v-model="forms[entity].createUpdate.data.capacity"
                                    hasDiv
                                    :title="TEXTS.form.capacity"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    :decimals="0"
                                    :minValue="0"
                                    :placeholder="TEXTS.form.placeholders.capacity"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.capacity"
                                    :aria-label="TEXTS.form.capacity"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:default>
                                        <i
                                            class="fa fa-info-circle cursor-pointer text-i-help mx-1"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            :title="TEXTS.form.capacityTooltip"
                                            :aria-label="TEXTS.form.capacityTooltip"></i>
                                    </template>
                                </InputNumber>
                                <InputText
                                    v-model="forms[entity].createUpdate.data.map_url"
                                    hasDiv
                                    :title="TEXTS.form.mapUrl"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    maxlength="255"
                                    :placeholder="TEXTS.form.placeholders.mapUrl"
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.map_url"
                                    :aria-label="TEXTS.form.mapUrl"
                                    xl="8"
                                    lg="8"/>
                                <InputSlot
                                    hasDiv
                                    :title="TEXTS.form.status"
                                    :titleClass="config?.forms?.classes?.title ? [config.forms.classes.title, 'fw-semibold'] : ['fw-semibold']"
                                    isRequired
                                    hasTextBottom
                                    :textBottomInfo="forms[entity].createUpdate.errors?.status"
                                    xl="4"
                                    lg="4">
                                    <template v-slot:input>
                                        <v-select
                                            v-model="forms[entity].createUpdate.data.status"
                                            :options="statuses"
                                            :class="config?.forms?.classes?.select2 || ''"
                                            :clearable="false"
                                            :searchable="false"
                                            :aria-label="TEXTS.form.status"/>
                                    </template>
                                </InputSlot>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal" :aria-label="TEXTS.modal.close" v-text="TEXTS.modal.close"></button>
                        <button type="button" :class="['btn waves-effect', isUpdate ? 'btn-warning' : 'btn-primary']" @click="saveEntity" :disabled="isSaving" :aria-label="TEXTS.modal.save" :aria-busy="isSaving">
                            <i class="fa fa-save" aria-hidden="true"></i>
                            <span class="ms-2" v-text="TEXTS.modal.save"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as Alerts      from "../../Helpers/Alerts.js";
import * as Constants   from "../../Helpers/Constants.js";
import * as Crud        from "../../Helpers/Crud.js";
import * as Forms       from "../../Helpers/Forms.js";
import * as Requests    from "../../Helpers/Requests.js";
import * as Utils       from "../../Helpers/Utils.js";

// ============================================
// MODULE CONFIGURATION
// ============================================
// This configuration object makes the component easily replicable for other modules
// Just change the values below to adapt to a new module
const MODULE_CONFIG = {
    entity: "branches",
    menuId: "menu-item-configuration-branches",
    pageTitle: "Sucursales",
    breadcrumbParent: "Configuración",
    perPage: 6
};

// ============================================
// FORM CONFIGURATION
// ============================================
// Form fields with default values
const FORM_FIELDS = {
    internal_code: "",
    name: "",
    address: "",
    reference: "",
    telephone: "",
    email: "",
    capacity: "",
    map_url: "",
    status: null
};

// Field preparation configuration (trim, normalize, toNumber, getCode)
const FORM_FIELD_CONFIG = {
    internal_code: {trim: true},
    name: {trim: true},
    address: {normalize: true},
    reference: {normalize: true},
    telephone: {normalize: true},
    email: {normalize: true},
    map_url: {normalize: true},
    capacity: {toNumber: true, minValue: 0},
    status: {getCode: true}
};

// Validation rules
// Explicit required: false for optional fields to maintain traceability
const VALIDATION_RULES = {
    internal_code: {required: true},
    name: {required: true},
    address: {required: false},
    reference: {required: false},
    telephone: {required: false},
    email: {required: false, email: true},
    capacity: {required: false, number: true, min: 0},
    map_url: {required: false, url: true},
    status: {required: true}
};

// Error labels for validation messages
const ERROR_LABELS = {
    internal_code: "Código interno",
    name: "Nombre",
    status: "Estado",
    email: "Correo electrónico",
    map_url: "URL del mapa",
    capacity: "Capacidad",
    required: "Es obligatorio"
};

// ============================================
// TEXT CONSTANTS
// ============================================
// All user-facing texts centralized for easy translation and maintenance
const TEXTS = {
    loading: `Loading ${MODULE_CONFIG.pageTitle}...`,
    filters: {
        filterBy: "Filtrar por",
        search: "Búsqueda"
    },
    actions: {
        search: "Buscar",
        add: "Agregar",
        edit: "Editar",
        viewMap: "Ver mapa"
    },
    card: {
        branch: "Sucursal",
        internalCode: "Código interno",
        notDefined: "No definido",
        status: "Estado",
        details: "Detalles",
        address: "Dirección",
        noAddress: "Sin dirección registrada",
        reference: "Referencia",
        telephone: "Teléfono",
        noTelephone: "Sin teléfono registrado",
        email: "Correo electrónico",
        noEmail: "Sin correo registrado",
        capacity: "Capacidad"
    },
    list: {
        totalItems: "sucursales encontradas",
        noData: "No hay sucursales registradas"
    },
    form: {
        internalCode: "Código interno",
        name: "Nombre",
        address: "Dirección",
        reference: "Referencia",
        telephone: "Teléfono",
        email: "Correo electrónico",
        capacity: "Capacidad",
        capacityTooltip: "Cantidad de personas",
        mapUrl: "URL del mapa",
        status: "Estado",
        placeholders: {
            internalCode: "Ej. SUC-001",
            name: "Ej. Sucursal Centro",
            address: "Ej. Av. Principal 123, Distrito",
            reference: "Ej. Frente al parque principal",
            telephone: "Ej. +51 999 999 999",
            email: "Ej. contacto@sucursal.com",
            capacity: "Ej. 40",
            mapUrl: "https://maps.google.com/..."
        }
    },
    modal: {
        close: "Cerrar",
        save: "Guardar"
    }
};

// ============================================
// FILTER OPTIONS
// ============================================
const FILTER_OPTIONS = [
    {code: "all", label: "Todos los filtros"},
    {code: "internal_code", label: "Código interno"},
    {code: "name", label: "Nombre"},
    {code: "address", label: "Dirección"},
    {code: "reference", label: "Referencia"},
    {code: "telephone", label: "Teléfono"},
    {code: "email", label: "Correo electrónico"}
];

export default {
    name: "BranchesMain",
    components: {},
    data() {

        // Initialize lists[entity] first - Vue evaluates computed properties during template compilation
        const initialEntity = {
            extras: {
                loading: false,
                route: ""
            },
            filters: {
                filter_by: FILTER_OPTIONS[0],
                word: ""
            },
            records: {
                total: 0,
                data: [],
                current_page: 1,
                last_page: 1,
                links: []
            }
        };

        // Initialize CRUD module structure
        const crudModule = Crud.initCrudModule({entity: MODULE_CONFIG.entity, menuId: MODULE_CONFIG.menuId, pageTitle: MODULE_CONFIG.pageTitle});

        // Ensure lists structure is properly initialized
        const entityLists = crudModule.lists[MODULE_CONFIG.entity] || {
            extras: {
                loading: false,
                route: ""
            },
            filters: {
                filter_by: FILTER_OPTIONS[0],
                word: ""
            },
            records: {
                total: 0
            }
        };

        // Ensure filters are initialized
        if(!Utils.isDefined({value: entityLists?.filters?.filter_by})) {

            entityLists.filters = {filter_by: this.filterByOptions[0], word: ""};

        }

        // Ensure forms structure is properly initialized
        const entityForms = crudModule.forms[MODULE_CONFIG.entity] || {
            createUpdate: {
                extras: {
                    modals: {
                        default: {
                            id: Utils.uuid(),
                            titles: {
                                store: `AGREGAR ${MODULE_CONFIG.pageTitle.toUpperCase()}`,
                                update: `EDITAR ${MODULE_CONFIG.pageTitle.toUpperCase()}`
                            }
                        }
                    }
                },
                data: Forms.initFormData(FORM_FIELDS),
                errors: {}
            }
        };

        // Helper: create modal titles - reusable across modules
        const createModalTitles = () => ({
            store: `AGREGAR ${MODULE_CONFIG.pageTitle.toUpperCase()}`,
            update: `EDITAR ${MODULE_CONFIG.pageTitle.toUpperCase()}`
        });

        // Ensure createUpdate structure exists
        if(!entityForms.createUpdate) {

            entityForms.createUpdate = {
                extras: {
                    modals: {
                        default: {
                            id: Utils.uuid(),
                            titles: createModalTitles()
                        }
                    }
                },
                data: Forms.initFormData(FORM_FIELDS),
                errors: {}
            };

        }else {

            // Ensure data initialized
            if(!entityForms.createUpdate.data) {

                entityForms.createUpdate.data = Forms.initFormData(FORM_FIELDS);

            }

            // Ensure errors initialized
            if(!entityForms.createUpdate.errors) {

                entityForms.createUpdate.errors = {};

            }

            // Ensure modals structure exists
            if(!entityForms.createUpdate.extras) {

                entityForms.createUpdate.extras = {
                    modals: {
                        default: {
                            id: Utils.uuid(),
                            titles: createModalTitles()
                        }
                    }
                };

            }else if(!entityForms.createUpdate.extras.modals) {

                entityForms.createUpdate.extras.modals = {
                    default: {
                        id: Utils.uuid(),
                        titles: createModalTitles()
                    }
                };

            }else if(!entityForms.createUpdate.extras.modals.default) {

                entityForms.createUpdate.extras.modals.default = {
                    id: Utils.uuid(),
                    titles: createModalTitles()
                };

            }

        }

        // Ensure config exists
        const finalConfig = crudModule.config || {
            ...Constants.generalConfig,
            entity: {
                ...Requests.config({entity: MODULE_CONFIG.entity}),
                page: {
                    title: MODULE_CONFIG.pageTitle,
                    active: true,
                    menu: {
                        id: MODULE_CONFIG.menuId
                    }
                }
            }
        };

        // Merge initial entity with crudModule data
        const entityData = {
            ...initialEntity,
            extras: {
                ...initialEntity.extras,
                route: entityLists.extras?.route || Requests.config({entity: MODULE_CONFIG.entity, type: "list"}) || ""
            },
            filters: {
                ...initialEntity.filters,
                filter_by: entityLists.filters?.filter_by || initialEntity.filters.filter_by,
                word: entityLists.filters?.word || initialEntity.filters.word
            }
        };

        // Build final return object - lists[entity] must exist as direct property
        const returnData = {
            TEXTS: TEXTS,
            FILTER_OPTIONS: FILTER_OPTIONS,
            MODULE_CONFIG: MODULE_CONFIG,
            lists: {
                [MODULE_CONFIG.entity]: entityData
            },
            forms: {
                [MODULE_CONFIG.entity]: entityForms
            },
            options: crudModule.options || {},
            config: finalConfig,
            isSaving: false,
            isInitialized: false
        };

        return returnData;

    },
    mounted: async function() {

        Utils.navbarItem("menu-item-configuration", {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});

        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams();
        const initOthers = await this.initOthers();

        if(initParams && initOthers) {

            this.isInitialized = true;

            Alerts.swals({show: false});
            this.listEntity({});

        }else {

            this.isInitialized = true;

        }

    },
    methods: {
        // ============================================
        // INITIALIZATION METHODS
        // ============================================
        async initParams() {

            const result = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "main"}, showAlert: true});

            this.options[MODULE_CONFIG.entity] = result.data?.config?.[MODULE_CONFIG.entity];

            return Requests.valid({result});

        },
        async initOthers() {

            return new Promise(resolve => {

                const entityList = this.lists[MODULE_CONFIG.entity];

                entityList.filters.filter_by = !Utils.isDefined({value: entityList.filters.filter_by}) ? this.filterByOptions[0] : entityList.filters.filter_by;

                resolve(true);

            });

        },

        // ============================================
        // LIST METHODS
        // ============================================
        async listEntity(params = null) {

            const entityList = this.lists[MODULE_CONFIG.entity];

            if(!entityList || !entityList.filters) {

                console.error(`${MODULE_CONFIG.pageTitle} list not initialized`);
                return;

            }

            // Handle both object {url} and string URL for backward compatibility
            const url        = typeof params === "object" && params !== null ? params.url : params;
            const filters    = Utils.cloneJson(entityList.filters);
            const filterData = {filter_by: filters?.filter_by?.code, word: filters.word, per_page: MODULE_CONFIG.perPage};

            entityList.extras.loading = true;

            try {

                let requestUrl = url || entityList.extras.route;
                let requestData = {};

                if(url) {

                    // Parse URL and ensure per_page parameter is present
                    const urlObj = new URL(url, window.location.origin);

                    if(!urlObj.searchParams.has("per_page")) {

                        urlObj.searchParams.set("per_page", MODULE_CONFIG.perPage);

                    }

                    // Preserve filter_by and word if they exist in URL
                    if(!urlObj.searchParams.has("filter_by") && filterData.filter_by) {

                        urlObj.searchParams.set("filter_by", filterData.filter_by);

                    }

                    if(!urlObj.searchParams.has("word") && filterData.word) {

                        urlObj.searchParams.set("word", filterData.word);

                    }

                    requestUrl = urlObj.pathname + urlObj.search;

                }else {

                    requestData = filterData;

                }

                const response = await Requests.get({route: requestUrl, data: requestData});

                entityList.records = response?.data ?? {total: 0, data: []};

            }catch(error) {

                console.error(`Error loading ${MODULE_CONFIG.pageTitle}:`, error);
                entityList.records = {total: 0, data: []};

            }finally {

                entityList.extras.loading = false;

            }

        },
        handleSearch() {

            this.listEntity({});

        },

        // ============================================
        // FORM METHODS
        // ============================================
        openModal(record = null) {

            const entityForms = this.forms[MODULE_CONFIG.entity];

            if(!entityForms?.createUpdate) {

                console.error(`${MODULE_CONFIG.pageTitle} form not initialized`);
                return;

            }

            // Clear form data and errors
            Forms.clearFormData(entityForms.createUpdate.data, FORM_FIELDS);
            entityForms.createUpdate.errors = {};

            if(Utils.isDefined({value: record})) {

                entityForms.createUpdate.data.id = record?.id;

                Object.keys(FORM_FIELDS).forEach(key => {

                    if(key === "status") {

                        entityForms.createUpdate.data.status = this.statuses.find(e => e.code === record?.status) || null;

                    }else {

                        entityForms.createUpdate.data[key] = record?.[key] ?? FORM_FIELDS[key];

                    }

                });

            }else {

                entityForms.createUpdate.data.internal_code = Utils.generateCode({length: 6});
                entityForms.createUpdate.data.status = this.statuses[0] || null;

            }

            Alerts.modals({type: "show", id: entityForms.createUpdate.extras?.modals?.default?.id});
            Alerts.tooltips({show: true, time: 500});

        },
        async saveEntity() {

            if(this.isSaving) return;

            const entityForms = this.forms[MODULE_CONFIG.entity];

            if(!entityForms?.createUpdate) {

                console.error(`${MODULE_CONFIG.pageTitle} form not initialized`);
                return;

            }

            Alerts.swals({});
            entityForms.createUpdate.errors = {};
            this.isSaving = true;

            try {

                const formData = Utils.cloneJson(entityForms.createUpdate.data);

                const validation = Forms.validateFormData(formData, VALIDATION_RULES, {isDescriptive: true, errorLabels: ERROR_LABELS});

                if(!validation.bool) {

                    Alerts.generateAlert({messages: Utils.getErrors({errors: validation.errors}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`});
                    return;

                }

                // Prepare data for submission
                const preparedData = Forms.prepareFormData(formData, FORM_FIELD_CONFIG);
                const isUpdate = Utils.isDefined({value: preparedData.id});
                const requestMethod = isUpdate ? Requests.patch : Requests.post;
                const route = isUpdate ? this.config.entity.routes.update : this.config.entity.routes.store;

                // Submit request
                const result = await requestMethod({route, data: preparedData, id: preparedData.id});

                if(Requests.valid({result})) {
                    // Success: close modal and refresh list
                    const modalId = entityForms.createUpdate.extras?.modals?.default?.id;

                    if(modalId) {

                        Alerts.modals({type: "hide", id: modalId});

                    }

                    Alerts.generateAlert({type: "success", msgContent: result?.data?.msg});

                    Forms.clearFormData(entityForms.createUpdate.data, FORM_FIELDS);
                    const entityList = this.lists[MODULE_CONFIG.entity];
                    const currentPage = entityList?.records?.current_page ?? 1;

                    this.listEntity({url: `${entityList?.extras?.route || ""}?page=${currentPage}`});

                }else {

                    entityForms.createUpdate.errors = result?.errors ?? {};

                    Alerts.toastrs({type: "error", subtitle: result?.data?.msg});
                    Alerts.swals({show: false});

                }

            }catch(error) {

                console.error(`Error saving ${MODULE_CONFIG.pageTitle}:`, error);

                Alerts.toastrs({type: "error", subtitle: `Error al guardar ${MODULE_CONFIG.pageTitle.toLowerCase()}. Por favor, intente nuevamente.`});
                Alerts.swals({show: false});

            }finally {

                this.isSaving = false;

            }

        },

        // ============================================
        // HELPER METHODS
        // ============================================
        isDefined(value) {

            return Utils.isDefined({value});

        },
        formatCapacity(capacity) {

            return Utils.formatCapacity(capacity);

        },
        getStatusBadgeClasses(status) {

            return Utils.getStatusBadgeClasses(status);

        },
        hasValidCapacity(capacity) {

            return Utils.isDefined({value: capacity}) && Utils.isNumber({value: capacity, minValue: 1});

        }
    },
    computed: {
        entity() {

            return MODULE_CONFIG.entity;

        },
        entityList() {

            if(!this.lists) {

                return {extras: {loading: false, route: ""}, filters: {filter_by: FILTER_OPTIONS[0], word: ""}, records: {total: 0, data: []}};

            }

            return this.lists[MODULE_CONFIG.entity] || {extras: {loading: false, route: ""}, filters: {filter_by: FILTER_OPTIONS[0], word: ""}, records: {total: 0, data: []}};

        },
        breadcrumbTitles() {

            return [{title: MODULE_CONFIG.breadcrumbParent}, this.config.entity.page];

        },
        filterByOptions() {

            return FILTER_OPTIONS;

        },
        statuses() {

            return (this.options?.[MODULE_CONFIG.entity]?.statuses ?? []).map(e => ({code: e.code, label: e.label}));

        },
        isUpdate() {

            const entityForms = this.forms[MODULE_CONFIG.entity];

            if(!entityForms || !entityForms.createUpdate || !entityForms.createUpdate.data) {

                return false;

            }

            return Utils.isDefined({value: entityForms.createUpdate.data.id});

        },
        modalTitles() {

            const entityForms = this.forms[MODULE_CONFIG.entity];

            if(!entityForms || !entityForms.createUpdate || !entityForms.createUpdate.extras || !entityForms.createUpdate.extras.modals || !entityForms.createUpdate.extras.modals.default) {

                return {
                    store: `AGREGAR ${MODULE_CONFIG.pageTitle.toUpperCase()}`,
                    update: `EDITAR ${MODULE_CONFIG.pageTitle.toUpperCase()}`
                };

            }

            return entityForms.createUpdate.extras.modals.default.titles;

        },
        filterByValue: {
            get() {

                const entityList = this.entityList;

                if(!entityList || !entityList.filters) {

                    return FILTER_OPTIONS[0];

                }

                return entityList.filters.filter_by || FILTER_OPTIONS[0];

            },
            set(value) {

                const entityList = this.entityList;

                if(entityList && entityList.filters) {

                    entityList.filters.filter_by = value;

                }

            }
        },

        filterWordValue: {
            get() {

                const entityList = this.entityList;

                if(!entityList || !entityList.filters) {

                    return "";

                }

                return entityList.filters.word || "";

            },
            set(value) {

                const entityList = this.entityList;

                if(entityList && entityList.filters) {

                    entityList.filters.word = value;

                }

            }
        },
        searchPlaceholder() {

            const entityList = this.entityList;

            if(!entityList || !entityList.filters || !entityList.filters.filter_by) {

                return "Buscar...";

            }

            const filterLabel = entityList.filters.filter_by.label || "...";
            return `Buscar por ${filterLabel.toLowerCase()}`;

        }

    }
};
</script>

<style scoped>
</style>
