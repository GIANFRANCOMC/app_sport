<template>
    <!-- <Breadcrumb :list="breadcrumbTitles"/> -->

    <!-- Content -->
    <div class="row g-3 mb-3">
        <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="d-flex flex-wrap justify-content-end align-items-center gap-1 h-100">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" v-model="forms.entity.createUpdate.config.show_actions" id="toggleActions" @change="changeFavorite(null, null)">
                    <label for="toggleActions" class="cursor-pointer">Mostrar acciones</label>
                </div>
                <div class="form-check form-switch ms-3">
                    <input class="form-check-input" type="checkbox" v-model="forms.entity.createUpdate.config.show_only_favorites" id="toggleFavs" @change="changeFavorite(null, null)">
                    <label for="toggleFavs" class="cursor-pointer">Mostrar solo favoritos</label>
                </div>
            </div>
        </div>
        <div class="col-xl-12 col-md-12 col-sm-12" v-if="forms.entity.createUpdate.config.show_actions">
            <div class="d-flex flex-wrap justify-content-end align-items-center gap-1 h-100">
                <div class="alert alert-info mb-2 mb-md-1 fw-semibold">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <span>Los cambios se aplicarán cuando actualices la página.</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div v-if="forms.entity.createUpdate.config.show_only_favorites && favoritesCompaniesSubSection.length === 0">
            <div class="col-xl-12 col-md-12 col-sm-12 text-center">
                <WithoutData type="image" text="Tu lista de favoritos está vacía."/>
            </div>
        </div>
        <template v-else>
            <div class="col-xl-4 col-md-6 col-sm-12" v-for="(section, indexSection) in sections" :key="indexSection">
                <div class="card border border-gray shadow-sm h-100">
                    <div class="card-body py-3">
                        <template v-if="!section?.has_sub_menu">
                            <button type="button"
                                    v-show="forms.entity.createUpdate.config.show_actions"
                                    :class="['position-absolute top-0 start-0 translate-middle badge badge-center rounded-pill border-light mx-3', getStatusEntity(section.sub_sections[0], 'menu') ? 'bg-success' : 'bg-secondary']"
                                    @click="changeFavorite(section, section.sub_sections[0], ['menu'])"
                                    @mouseenter="setHoveredeEntity(section.sub_sections[0], 'menu', true)"
                                    @mouseleave="setHoveredeEntity(section.sub_sections[0], 'menu', false)">
                                <i :class="['fa-xs', getStatusEntity(section.sub_sections[0], 'menu') ? 'fa fa-bars' : 'fa fa-bars fa-beat-fade']" style="--fa-beat-fade-opacity: 0.67; --fa-beat-fade-scale: 1.075;"></i>
                            </button>
                            <button type="button"
                                    v-show="forms.entity.createUpdate.config.show_actions"
                                    :class="['position-absolute top-0 start-10 translate-middle badge badge-center rounded-pill border-light mx-4', getStatusEntity(section.sub_sections[0], 'favorite') ? 'bg-warning' : 'bg-secondary']"
                                    @click="changeFavorite(section, section.sub_sections[0], ['favorite'])"
                                    @mouseenter="setHoveredeEntity(section.sub_sections[0], 'favorite', true)"
                                    @mouseleave="setHoveredeEntity(section.sub_sections[0], 'favorite', false)">
                                <i :class="['fa-xs', getStatusEntity(section.sub_sections[0], 'favorite') ? 'fa fa-star' : 'fa-regular fa-star fa-beat-fade']" style="--fa-beat-fade-opacity: 0.67; --fa-beat-fade-scale: 1.075;"></i>
                            </button>
                            <div class="d-flex align-items-center justify-content-start h-100">
                                <!-- <i :class="[section?.dom_icon, 'fa-xl']"></i> -->
                                <a class="mb-0 ms-3 fw-bold text-dark h4" v-text="section?.order_company+'. '+section?.dom_label" :href="section.sub_sections[0]?.dom_route_url"></a>
                                <i class="fa fa-star text-warning ms-2" v-if="!forms.entity.createUpdate.config.show_actions && getValueEntity(section.sub_sections[0])?.preferenceValue?.is_favorite"></i>
                                <a class="ms-auto fs-2" :href="section.sub_sections[0]?.dom_route_url">
                                    <i class="fa-solid fa-circle-arrow-right text-info-1"></i>
                                </a>
                            </div>
                        </template>
                        <template v-else>
                            <div class="d-flex align-items-center justify-content-start mb-2">
                                <!-- <i :class="[section?.dom_icon, 'fa-xl']"></i> -->
                               <span class="mb-0 ms-3 fw-bold text-dark h4" v-text="section?.order_company+'. '+section?.dom_label"></span>
                            </div>
                            <div>
                                <template v-for="(subSection, indexSubSection) in section?.sub_sections" :key="indexSubSection">
                                    <div class="d-flex align-items-center justify-content-start ps-2 py-1 gap-2">
                                        <button type="button"
                                                v-show="forms.entity.createUpdate.config.show_actions"
                                                :class="['badge badge-center rounded-pill border-light', getStatusEntity(subSection, 'menu') ? 'bg-success' : 'bg-secondary']"
                                                @click="changeFavorite(section, subSection, ['menu'])"
                                                @mouseenter="setHoveredeEntity(subSection, 'menu', true)"
                                                @mouseleave="setHoveredeEntity(subSection, 'menu', false)">
                                            <i :class="['fa-xs', getStatusEntity(subSection, 'menu') ? 'fa fa-bars' : 'fa fa-bars fa-beat-fade']" style="--fa-beat-fade-opacity: 0.67; --fa-beat-fade-scale: 1.075;"></i>
                                        </button>
                                        <button type="button"
                                                v-show="forms.entity.createUpdate.config.show_actions"
                                                :class="['badge badge-center rounded-pill border-light', getStatusEntity(subSection, 'favorite') ? 'bg-warning' : 'bg-secondary']"
                                                @click="changeFavorite(section, subSection, ['favorite'])"
                                                @mouseenter="setHoveredeEntity(subSection, 'favorite', true)"
                                                @mouseleave="setHoveredeEntity(subSection, 'favorite', false)">
                                            <i :class="['fa-xs', getStatusEntity(subSection, 'favorite') ? 'fa fa-star' : 'fa-regular fa-star fa-beat-fade']" style="--fa-beat-fade-opacity: 0.67; --fa-beat-fade-scale: 1.075;"></i>
                                        </button>
                                        <ul :class="['my-0', forms.entity.createUpdate.config.show_actions ? 'list-unstyled' : '']">
                                            <li>
                                                <a class="text-info-1 fw-semibold ms-2" v-text="subSection?.dom_label" :href="subSection?.dom_route_url"></a>
                                                <i class="fa fa-star text-warning ms-2" v-if="!forms.entity.createUpdate.config.show_actions && getValueEntity(subSection)?.preferenceValue?.is_favorite"></i>
                                            </li>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import * as Alerts    from "../../Helpers/Alerts.js";
import * as Constants from "../../Helpers/Constants.js";
import * as Requests  from "../../Helpers/Requests.js";
import * as Utils     from "../../Helpers/Utils.js";

export default {
    components: {
        //
    },
    mounted: async function() {

        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        let initParams = await this.initParams({}),
            initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});

        }

    },
    data() {
        return {
            forms: {
                entity: {
                    createUpdate: {
                        extras: {
                            modals: {
                                default: {
                                    id: Utils.uuid(),
                                    titles: {
                                        store: "Agregar",
                                        update: "Editar"
                                    }
                                }
                            }
                        },
                        data: {},
                        config: {
                            show_actions: false,
                            show_only_favorites: false
                        },
                        errors: {}
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "home"}),
                    page: {
                        title: "Inicio",
                        active: true,
                        menu: {
                            id: "menu-item-home"
                        }
                    }
                }
            }
        };
    },
    methods: {
        // Init
        async initParams({}) {

            let initParams = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "main"}, showAlert: true});

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.forms.entity.createUpdate.config.show_actions = this.preferenceCompaniesSubSection?.show_actions;
                this.forms.entity.createUpdate.config.show_only_favorites = this.preferenceCompaniesSubSection?.show_only_favorites;

                resolve(true);

            });

        },
        // Entity forms
        setHoveredeEntity(record = null, type = "", value) {

            if(!this.isDefined({value: record?.hovered})) {

                record.hovered = {
                    menu: false,
                    favorite: false
                };

            }

            record.hovered[type] = value;

        },
        getValueEntity(record = null) {

            let filtered = (this.preferenceCompaniesSubSection?.sub_sections ?? []).filter(e => Number(e?.sub_section_id) == record?.id);

            const isNew = filtered.length === 0;
            const preferenceValue = isNew ? null : filtered[0];

            return {isNew, preferenceValue};

        },
        getStatusEntity(record = null, type = "") {

            if(!this.isDefined({value: record})) return false;

            const isHovered = record?.hovered?.[type] ?? false;

            let isVisible = false;

            if(["menu"].includes(type)) {

                isVisible = this.visiblesCompaniesSubSection.includes(record.id);

            }else if(["favorite"].includes(type)) {

                isVisible = this.favoritesCompaniesSubSection.includes(record.id);

            }

            return isHovered ? !isVisible : isVisible;

        },
        async changeFavorite(section = null, subSection = null, types = []) {

            let data = {
                id: subSection?.id
            };

            let msgAlert = "",
                confirmAlert = "";

            if(types.length > 0) {

                const {isNew, preferenceValue} = this.getValueEntity(data);

                let hasSubMenu = section?.has_sub_menu;

                const recordName = `${section?.dom_label}`+`${hasSubMenu ? " | "+subSection?.dom_label : ""}`;

                if(["menu"].includes(types[0])) {

                    data.visible_in_menu = isNew ? false : !preferenceValue?.visible_in_menu;

                    msgAlert = data.visible_in_menu ? `¿Deseas mostrar <b>${recordName}</b> este módulo en el menú lateral?` : `¿Deseas ocultar <b>${recordName}</b> este módulo del menú lateral?`;
                    confirmAlert = data.visible_in_menu ? `mostrar` : `ocultar`;

                }

                if(["favorite"].includes(types[0])) {

                    data.is_favorite = isNew ? true : !preferenceValue?.is_favorite;

                    msgAlert = data.is_favorite ? `¿Deseas agregar <b>${recordName}</b> este acceso a tus favoritos?` : `¿Deseas quitar <b>${recordName}</b> este acceso de tus favoritos?`;
                    confirmAlert = data.is_favorite ? `agregar` : `quitar`;

                }

            }

            data.show_actions = this.forms.entity.createUpdate.config.show_actions;
            data.show_only_favorites = this.forms.entity.createUpdate.config.show_only_favorites;

            let el = this;

            const updatePreferences = async () => {

                let form = Utils.cloneJson(data);

                const store = await Requests.patch({route: el.config.entity.routes.store, data: form, id: subSection?.id ?? 0});

                if(Requests.valid({result: store})) {

                    // Alerts.toastrs({type: "success", subtitle: store?.data?.msg});

                    el.config.essential.preferences = window.preferences = store?.data?.preferences || [];

                }else {

                    // Alerts.toastrs({type: "error", subtitle: store?.data?.msg});

                }

            };

            if(types.length > 0) {

                Swal.fire({
                    html: `<span>${msgAlert}</span>`,
                    icon: "warning",
                    allowOutsideClick: false,
                    showCancelButton: true,
                    confirmButtonText: `Sí, ${confirmAlert}`,
                    cancelButtonText: "Cancelar",
                    customClass: {
                        confirmButton: "btn btn-primary waves-effect",
                        cancelButton: "btn btn-secondary waves-effect ms-3"
                    }
                })
                .then(async function(result) {

                    if(result.isConfirmed) {

                        await updatePreferences();

                    }else if(result.isDismissed) {

                        //

                    }

                });

            }else {

                await updatePreferences();

            }

        },
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        }
    },
    computed: {
        breadcrumbTitles: function() {

            return [this.config.entity.page];

        },
        preferenceCompaniesSubSection: function() {

            return this.config?.essential?.preferences?.config_companies_sub_sections;

        },
        visiblesCompaniesSubSection: function() {

            let subSectionIds = this.sections.flatMap(e => Array.isArray(e.sub_sections) ? e.sub_sections.map(sub => Number(sub?.id)) : []);

            const valuePreferences   = this.preferenceCompaniesSubSection?.sub_sections ?? [];
            const visiblePreferences = valuePreferences.filter(e => e?.visible_in_menu).map(e => Number(e?.sub_section_id));
            const allPreferences     = valuePreferences.map(e => Number(e?.sub_section_id));

            return this.isDefined({value: this.preferenceCompaniesSubSection?.sub_sections}) ?
                        subSectionIds.filter(e => !allPreferences.includes(e) || visiblePreferences.includes(e)) :
                        subSectionIds;

        },
        favoritesCompaniesSubSection: function() {

            let subSectionIds = this.sections.flatMap(e => Array.isArray(e.sub_sections) ? e.sub_sections.map(sub => Number(sub?.id)) : []);

            const valuePreferences    = this.preferenceCompaniesSubSection?.sub_sections ?? [];
            const favoritePreferences = valuePreferences.filter(e => e?.is_favorite).map(e => Number(e?.sub_section_id));
            const allPreferences      = valuePreferences.map(e => Number(e?.sub_section_id));

            return this.isDefined({value: this.preferenceCompaniesSubSection?.sub_sections}) ?
                        subSectionIds.filter(e => favoritePreferences.includes(e)) :
                        [];

        },
        sections: function() {

            let filtered = this.config?.essential?.sections ?? [];

            if(this.forms.entity.createUpdate.config.show_only_favorites) {

                filtered = filtered.filter(e => e.sub_sections?.some(sub => this.favoritesCompaniesSubSection.includes(sub.id)));

            }

            return filtered;

        }
    }
};
</script>
