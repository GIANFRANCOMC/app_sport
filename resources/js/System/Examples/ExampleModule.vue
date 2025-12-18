<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Filtros -->
    <FiltersSection
        :filter-by-value="lists.entity.filters.filter_by"
        :filter-word-value="lists.entity.filters.word"
        :filter-by-options="filterByOptions"
        :loading="lists.entity.extras.loading"
        @update:filterByValue="lists.entity.filters.filter_by = $event"
        @update:filterWordValue="lists.entity.filters.word = $event"
        @search="listEntity({})"
        @add="modalCreateUpdateEntity({})"/>

    <!-- Tabla -->
    <DataTable
        :columns="tableColumns"
        :records="lists.entity.records.data"
        :loading="lists.entity.extras.loading"
        :actions="tableActions"
        :pagination-links="lists.entity.records.links"
        @action="handleTableAction"
        @pageChange="listEntity"/>

    <!-- Modal de formulario -->
    <FormModal
        :modal-id="forms.entity.createUpdate.extras.modals.default.id"
        :title="forms.entity.createUpdate.extras.modals.default.titles[isUpdate ? 'update' : 'store']"
        @submit="createUpdateEntity">
        <template v-slot:body>
            <div class="row g-3">
                <InputText
                    v-model="forms.entity.createUpdate.data.name"
                    hasDiv
                    title="Nombre"
                    isRequired
                    maxlength="100"
                    hasTextBottom
                    :textBottomInfo="forms.entity.createUpdate.errors?.name"
                    xl="12"
                    lg="12"/>
                <InputText
                    v-model="forms.entity.createUpdate.data.description"
                    hasDiv
                    title="Descripción"
                    maxlength="200"
                    hasTextBottom
                    :textBottomInfo="forms.entity.createUpdate.errors?.description"
                    xl="12"
                    lg="12"/>
                <InputSlot
                    hasDiv
                    title="Estado"
                    isRequired
                    hasTextBottom
                    :textBottomInfo="forms.entity.createUpdate.errors?.status"
                    xl="6"
                    lg="6">
                    <template v-slot:input>
                        <v-select
                            v-model="forms.entity.createUpdate.data.status"
                            :options="statuses"
                            :clearable="false"
                            :searchable="false"/>
                    </template>
                </InputSlot>
            </div>
        </template>
    </FormModal>
</template>

<script>
import { CrudMixin } from "@System/Helpers/CrudMixin.js";
import { createModuleConfig, createFilterByOptions } from "@System/Helpers/ModuleFactory.js";
import { createTableActions } from "@System/Helpers/ComponentHelpers.js";
import { handleCreateUpdateResponse } from "@System/Helpers/FormHelpers.js";
import { validateForm } from "@System/Helpers/ValidationHelpers.js";
import { CommonValidationRules } from "@System/Helpers/ValidationHelpers.js";
import * as Requests from "@System/Helpers/Requests.js";
import * as Alerts from "@System/Helpers/Alerts.js";
import * as Utils from "@System/Helpers/Utils.js";
import DataTable from "@System/Components/Generics/DataTable.vue";
import FormModal from "@System/Components/Generics/FormModal.vue";
import StatusBadge from "@System/Components/Generics/StatusBadge.vue";
import FiltersSection from "@System/Components/Generics/FiltersSection.vue";

export default {
    mixins: [CrudMixin],
    components: {
        DataTable,
        FormModal,
        StatusBadge,
        FiltersSection
    },
    data() {
        const moduleConfig = createModuleConfig({
            entity: "example",
            menuId: "menu-example",
            pageTitle: "Ejemplo",
            parentMenuId: "menu-parent",
            defaultFormData: {
                name: "",
                description: "",
                status: null
            }
        });

        return {
            ...moduleConfig,
            tableColumns: [
                {key: "id", label: "ID", style: {width: "10%"}},
                {key: "name", label: "Nombre", style: {width: "40%"}},
                {key: "description", label: "Descripción", style: {width: "30%"}},
                {
                    key: "status",
                    label: "Estado",
                    type: "badge",
                    style: {width: "20%"},
                    formatter: (value, record) => record.formatted_status
                }
            ],
            tableActions: createTableActions()
        };
    },
    computed: {
        breadcrumbTitles() {
            return [{title: "Ejemplo"}, this.config.entity.page];
        },
        filterByOptions() {
            return createFilterByOptions();
        },
        statuses() {
            return this.options?.example?.statuses?.map(e => ({
                code: e.code,
                label: e.label
            })) || [];
        }
    },
    methods: {
        // Sobrescribir métodos del mixin si es necesario
        async createUpdateEntity() {
            const functionName = "createUpdateEntity";
            
            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.data);
            
            // Validación
            const validationRules = {
                name: CommonValidationRules.required,
                status: CommonValidationRules.required
            };
            
            const validateResult = validateForm(form, validationRules, {
                isDescriptive: true,
                errorLabels: {
                    name: "Nombre",
                    status: "Estado"
                }
            });

            if (!validateResult.bool) {
                Alerts.generateAlert({
                    messages: Utils.getErrors({errors: validateResult.errors}),
                    msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`
                });
                return;
            }

            // Preparar datos
            form.status = form.status?.code || form.status;

            // Enviar
            const isUpdate = Utils.isDefined({value: form.id});
            const response = await (isUpdate
                ? Requests.patch({route: this.config.entity.routes.update, data: form, id: form.id})
                : Requests.post({route: this.config.entity.routes.store, data: form}));

            // Manejar respuesta
            await handleCreateUpdateResponse(response, {
                modalId: this.forms.entity.createUpdate.extras.modals.default.id,
                formErrorsObject: this.forms.entity.createUpdate.errors,
                reloadList: () => this.listEntity({
                    url: `${this.lists.entity.extras.route}?page=${this.lists.entity.records?.current_page ?? 1}`
                })
            });
        },

        handleTableAction({action, record}) {
            if (action === "edit") {
                this.modalCreateUpdateEntity({record});
            } else if (action === "delete") {
                this.deleteEntity(record);
            }
        },

        async deleteEntity(record) {
            // Implementar lógica de eliminación
            console.log("Delete:", record);
        },

        clearForm({functionName}) {
            if (["modalCreateUpdateEntity", "createUpdateEntity"].includes(functionName)) {
                this.forms.entity.createUpdate.data = {
                    id: null,
                    name: "",
                    description: "",
                    status: null
                };
            }
        },

        validateForm({functionName, form, extras}) {
            if (functionName === "createUpdateEntity") {
                const validationRules = {
                    name: CommonValidationRules.required,
                    status: CommonValidationRules.required
                };
                
                return validateForm(form, validationRules, {
                    isDescriptive: extras?.type === "descriptive",
                    errorLabels: {
                        name: "Nombre",
                        status: "Estado"
                    }
                });
            }
            
            return {bool: true};
        }
    }
};
</script>

