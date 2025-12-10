<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row g-4">
        <div class="col-lg-9 col-12">
            <div class="card invoice-preview-card">
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <InputSlot
                            hasDiv
                            title="Sucursal"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.branch"
                            xl="7"
                            lg="12">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.branch"
                                    :options="branches"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            title="Tipo de comprobante"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.serie"
                            xl="5"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.serie"
                                    :options="series"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione">
                                </v-select>
                            </template>
                        </InputSlot>
                        <InputDate
                            v-model="forms.entity.createUpdate.data.issue_date"
                            hasDiv
                            title="Fecha de emisión"
                            isRequired
                            disabled
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.issue_date"
                            xl="3"
                            lg="6"/>
                        <InputSlot
                            hasDiv
                            title="Cliente"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.holder"
                            xl="9"
                            lg="12">
                            <template v-slot:default>
                                <AddCustomer
                                    :options="options"
                                    @postAction="addCustomerPostAction"/>
                            </template>
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.holder"
                                    :options="holders"
                                    :clearable="false"
                                    placeholder="Seleccione"/>
                            </template>
                        </InputSlot>
                        <!-- <InputSlot
                            hasDiv
                            title="Tipo de moneda"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.currency"
                            xl="3"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.data.currency"
                                    :options="currencies"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione">
                                    <template #option="{ label, data }">
                                        <span v-text="label" class="d-block fw-bold"></span>
                                        <small v-text="'('+data?.sign+')'" class="d-block"></small>
                                    </template>
                                </v-select>
                            </template>
                        </InputSlot> -->
                    </div>
                    <div class="row g-3">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr class="text-center align-middle">
                                        <th class="bg-secondary text-white fw-semibold col-auto">#</th>
                                        <th class="bg-secondary text-white fw-semibold col-1">DESCRIPCIÓN</th>
                                        <th class="bg-secondary text-white fw-semibold col-3">CANTIDAD</th>
                                        <th class="bg-secondary text-white fw-semibold min-w-150px">PRECIO UNITARIO</th>
                                        <th class="bg-secondary text-white fw-semibold min-w-150px">TOTAL</th>
                                        <th class="bg-secondary text-white fw-semibold col-auto"></th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0 bg-white">
                                    <template v-if="(forms.entity.createUpdate.data.details).length > 0">
                                        <template v-for="(record, keyRecord) in forms.entity.createUpdate.data.details" :key="record.id">
                                            <tr class="text-center">
                                                <td>
                                                    <span v-text="keyRecord + 1" class="badge rounded-pill bg-info-1 fw-bold"></span>
                                                </td>
                                                <td class="fw-semibold text-start">
                                                    <span class="text-break" v-text="record.name"></span>
                                                </td>
                                                <td>
                                                    <InputNumber
                                                        v-model="record.quantity"
                                                        @change="calculateDuration({record})"
                                                        :decimals="getItemDecimals({mode: 'result', record})"/>
                                                    <div class="d-block mt-1">
                                                        <button class="btn btn-danger btn-xs waves-effect" type="button" @click="changeQuantityDetail({record, keyRecord, type: 'subtract'})">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                        <button class="btn btn-info-1 btn-xs waves-effect" type="button" @click="changeQuantityDetail({record, keyRecord, type: 'add'})">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <InputNumber v-model="record.price">
                                                        <template v-slot:inputGroupPrepend v-if="isDefined({value: record?.currency})">
                                                            <button class="btn btn-primary waves-effect px-2" type="button">
                                                                <small v-text="record?.currency?.sign"></small>
                                                            </button>
                                                        </template>
                                                    </InputNumber>
                                                </td>
                                                <td>
                                                    <span class="text-break fw-semibold">
                                                        <small v-text="record.currency?.sign ?? ''"></small>
                                                        <small v-text="separatorNumber(calculateTotal({item: record}))" class="ms-2"></small>
                                                    </span>
                                                </td>
                                                <td>
                                                    <InputSlot
                                                        hasDiv
                                                        :isInputGroup="false"
                                                        :divInputClass="['d-flex flex-wrap justify-content-center gap-2 gap-md-1']"
                                                        xl="12"
                                                        lg="12">
                                                        <template v-slot:input>
                                                            <button class="btn btn-danger btn-xs waves-effect" type="button" @click="deleteDetail({record, keyRecord})">
                                                                <i class="fa fa-times"></i>
                                                                <span class="ms-1">Eliminar</span>
                                                            </button>
                                                            <button v-if="['product', 'service'].includes(record?.type)" class="btn btn-info-1 btn-xs waves-effect" type="button" @click="duplicateDetail({record, keyRecord})">
                                                                <i class="fa fa-copy"></i>
                                                                <span class="ms-1">Duplicar</span>
                                                            </button>
                                                            <!-- <template v-if="isSubscription(record?.type)">
                                                                <button class="btn btn-success btn-xs waves-effect" type="button" @click="viewDetail({record, keyRecord})">
                                                                    <i :class="record?.extras?.showDetail ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                                                    <span class="ms-1" v-text="record?.extras?.showDetail ? 'Ocultar detalle' : 'Mostar detalle'"></span>
                                                                </button>
                                                            </template> -->
                                                        </template>
                                                    </InputSlot>
                                                </td>
                                            </tr>
                                            <template v-if="record?.extras?.showDetail">
                                                <template v-if="isSubscription(record?.type)">
                                                    <tr class="border-transparent text-center">
                                                        <td colspan="1"></td>
                                                        <td colspan="5">
                                                            <div class="row justify-content-center g-2 my-1">
                                                                <div class="col-4">
                                                                    <InputDatetime
                                                                        title="Fecha de inicio"
                                                                        v-model="record.extras.start_date"
                                                                        @change="calculateDuration({record})"
                                                                        isRequired/>
                                                                </div>
                                                                <div class="col-4">
                                                                    <InputDatetime
                                                                        title="Fecha de finalización"
                                                                        v-model="record.extras.end_date"
                                                                        isRequired
                                                                        disabled/>
                                                                </div>
                                                                <InputSlot
                                                                    v-if="false"
                                                                    :isInputGroup="false"
                                                                    :divInputClass="['col-12', 'text-start', 'mt-3']">
                                                                    <template v-slot:input>
                                                                        <div v-if="['day', 'month', 'year'].includes(record.extras?.duration_type)" class="form-check form-check-primary my-1">
                                                                            <label class="form-check-label fw-semibold">
                                                                                <input
                                                                                    class="form-check-input"
                                                                                    type="checkbox"
                                                                                    v-model="record.extras.set_end_of_day"
                                                                                    @change="calculateDuration({record})"/>
                                                                                Ajustar la hora de la Fecha de finalización al final del día (23:59 = 11:59 PM)
                                                                            </label>
                                                                        </div>
                                                                        <!--<div class="form-check form-check-primary my-2">
                                                                            <label class="form-check-label">
                                                                                <input
                                                                                    class="form-check-input"
                                                                                    type="checkbox"
                                                                                    v-model="record.extras.force"/>
                                                                                Tomar en cuenta la membresías activas
                                                                            </label>
                                                                        </div>-->
                                                                    </template>
                                                                </InputSlot>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-transparent">
                                                        <td colspan="6">
                                                            <div class="divider text-center divider-info">
                                                                <div class="divider-text">
                                                                    <div class="badge rounded-pill bg-label-info px-5 py-1">
                                                                        <i class="fa fa-info-circle"></i>
                                                                        <span class="ms-2 fw-bold h6 text-info" v-text="'Detalle #'+(keyRecord + 1)"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </template>
                                        </template>
                                        <tr class="fs-5">
                                            <td colspan="4" class="fw-bold text-end">TOTAL :</td>
                                            <td colspan="2" class="fw-bold text-start">
                                                <span class="text-break">
                                                    <span v-text="forms.entity.createUpdate.data.currency?.data?.sign ?? ''"></span>
                                                    <span v-text="separatorNumber(total)" class="ms-2"></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td class="text-center" colspan="99">
                                                <WithoutData type="image"/>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <!-- <small :class="config.forms.errors.styles.default" v-html="isDefined({value: forms.entity.createUpdate.errors?.details}) ? forms.entity.createUpdate.errors?.details : ''"></small> -->
                    </div>
                    <div class="row justify-content-end g-3 my-1">
                        <div class="col-lg-auto col-sm-auto">
                            <a href="javascript:void(0)" @click="modalAddDetail({})" class="fw-bold">
                                <i class="fa fa-plus-circle"></i>
                                <span class="ms-1">Agregar detalle</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <InputTextArea
                            v-model="forms.entity.createUpdate.data.observation"
                            hasDiv
                            :divClass="['mb-3', 'p-0']"
                            title="Observaciones"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.errors?.observation"/>
                        <button class="btn btn-info-1 waves-effect my-1" @click="viewSubscriptions({})">
                            <i class="fa-solid fa-binoculars"></i>
                            <span class="ms-2">Ver membresías</span>
                        </button>
                        <button class="btn btn-primary waves-effect my-1" @click="modalAddDetail({})">
                            <i class="fa fa-plus"></i>
                            <span class="ms-2">Agregar detalle</span>
                        </button>
                        <button class="btn btn-success waves-effect my-1" @click="createUpdateEntity()">
                            <i class="fa-solid fa-cash-register"></i>
                            <span class="ms-2">Generar venta</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.details.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.details.titles[forms.entity.createUpdate.extras.modals.details.data?.mode]"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <InputSlot
                            hasDiv
                            title="Catálogo comercial"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.extras.modals.details.errors?.item">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms.entity.createUpdate.extras.modals.details.data.item"
                                    :options="items"
                                    :clearable="false"
                                    placeholder="Seleccione">
                                    <template #option="{ label, data }">
                                        <span v-text="label" class="d-block fw-bold"></span>
                                        <div class="d-block">
                                            <small>
                                                <i class="fa fa-star text-warning"></i>
                                            </small>
                                            <small v-text="data?.formatted_type" class="ms-2"></small>
                                            <small v-text="data?.currency?.sign" class="ms-2"></small>
                                            <small v-text="separatorNumber(data?.price)" class="ms-1"></small>
                                        </div>
                                        <div class="d-block" v-if="isDefined({value: data?.min_price}) || isDefined({value: data?.max_price})">
                                            <small>
                                                <i class="fa fa-money-bill text-success"></i>
                                            </small>
                                            <small class="ms-2 colon-at-end">Rango de precios</small>
                                            <small v-if="isDefined({value: data?.min_price})" v-text="'Min: '+data?.currency?.sign+data?.min_price" class="ms-2 text-danger fw-bold"></small>
                                            <small v-if="isDefined({value: data?.max_price})" v-text="'Max: '+data?.currency?.sign+data?.max_price" class="ms-2 text-success fw-bold"></small>
                                        </div>
                                        <template v-if="isSubscription(data?.type)">
                                            <div class="d-block">
                                                <small>
                                                    <i class="fa fa-clock text-info"></i>
                                                </small>
                                                <small class="ms-2">Duración:</small>
                                                <small v-text="data?.formatted_duration" class="ms-2"></small>
                                            </div>
                                        </template>
                                    </template>
                                </v-select>
                            </template>
                        </InputSlot>
                        <InputSlot
                            v-if="isDefined({value: forms.entity.createUpdate.extras.modals.details.data.extras?.min_price}) || isDefined({value: forms.entity.createUpdate.extras.modals.details.data.extras?.max_price})"
                            hasDiv
                            :isInputGroup="false"
                            :divInputClass="['d-flex flex-wrap justify-content-start align-items-center gap-2 gap-md-2']"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <span class="fw-bold colon-at-end">Rango de precios</span>
                                <span v-if="isDefined({value: forms.entity.createUpdate.extras.modals.details.data.extras?.min_price})" v-text="'Min: '+forms.entity.createUpdate.extras.modals.details.data.item?.data?.currency?.sign+' '+separatorNumber(forms.entity.createUpdate.extras.modals.details.data.extras?.min_price)" class="fw-semibold text-danger"></span>
                                <span v-if="isDefined({value: forms.entity.createUpdate.extras.modals.details.data.extras?.max_price})" v-text="'Max: '+forms.entity.createUpdate.extras.modals.details.data.item?.data?.currency?.sign+' '+separatorNumber(forms.entity.createUpdate.extras.modals.details.data.extras?.max_price)" class="fw-semibold text-success"></span>
                            </template>
                        </InputSlot>
                        <InputNumber
                            v-model="forms.entity.createUpdate.extras.modals.details.data.quantity"
                            @change="calculateDuration({record: forms.entity.createUpdate.extras.modals.details.data})"
                            hasDiv
                            :title="isSubscription(forms.entity.createUpdate.extras.modals.details.data.type) ? 'Cantidad de Periodos' : 'Cantidad'"
                            isRequired
                            :decimals="getItemDecimals({mode: 'result', record: forms.entity.createUpdate.extras.modals.details.data})"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.extras.modals.details.errors?.quantity"
                            xl="4"
                            lg="4"/>
                        <InputNumber
                            v-model="forms.entity.createUpdate.extras.modals.details.data.price"
                            hasDiv
                            title="Precio"
                            isRequired
                            :minValue="forms.entity.createUpdate.extras.modals.details.data.extras?.min_price"
                            :maxValue="forms.entity.createUpdate.extras.modals.details.data.extras?.max_price"
                            hasTextBottom
                            :textBottomInfo="forms.entity.createUpdate.extras.modals.details.errors?.price"
                            xl="4"
                            lg="4">
                            <template v-slot:inputGroupPrepend>
                                <template v-if="isDefined({value: forms.entity.createUpdate.extras.modals.details.data.item?.data?.currency})">
                                    <button class="btn btn-primary waves-effect pe-none" type="button" v-text="forms.entity.createUpdate.extras.modals.details.data.item?.data?.currency?.sign"></button>
                                </template>
                            </template>
                        </InputNumber>
                        <InputSlot
                            hasDiv
                            title="Total"
                            isRequired
                            xl="4"
                            lg="4">
                            <template v-slot:inputGroupPrepend>
                                <template v-if="isDefined({value: forms.entity.createUpdate.data.currency?.data})">
                                    <button class="btn btn-primary waves-effect pe-none" type="button" v-text="forms.entity.createUpdate.data.currency?.data?.sign"></button>
                                </template>
                            </template>
                            <template v-slot:input>
                                <input class="form-control" disabled :value="separatorNumber(totalModalDetail)"/>
                            </template>
                        </InputSlot>
                    </div>
                    <template v-if="isSubscription(forms.entity.createUpdate.extras.modals.details.data.type)">
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-header bg-success text-white py-2">
                                    <span class="fw-semibold">Detalle de la membresía</span>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mt-1 mb-3">
                                        <InputDatetime
                                            v-model="forms.entity.createUpdate.extras.modals.details.data.extras.start_date"
                                            @change="calculateDuration({record: forms.entity.createUpdate.extras.modals.details.data})"
                                            hasDiv
                                            title="Fecha de inicio"
                                            isRequired
                                            hasTextBottom
                                            :textBottomInfo="forms.entity.createUpdate.extras.modals.details.errors?.extras_start_date"
                                            xl="6"
                                            lg="6"/>
                                        <InputDatetime
                                            v-model="forms.entity.createUpdate.extras.modals.details.data.extras.end_date"
                                            hasDiv
                                            title="Fecha de finalización"
                                            isRequired
                                            disabled
                                            hasTextBottom
                                            :textBottomInfo="forms.entity.createUpdate.extras.modals.details.errors?.extras_end_date"
                                            xl="6"
                                            lg="6"/>
                                        <InputSlot
                                            :isInputGroup="false"
                                            :divInputClass="['col-12', 'text-start', 'mt-3']">
                                            <template v-slot:input>
                                                <div class="d-flex align-items-center my-1 flex-wrap">
                                                    <h5 class="mb-0 d-flex align-items-center">
                                                        <div class="badge bg-label-dark fw-semibold">
                                                            <i class="fa fa-calculator text-dark"></i>
                                                            <span class="ms-2">Duración total calculada:</span>
                                                        </div>
                                                    </h5>
                                                    <h5 class="ms-2 mb-0 d-flex align-items-center">
                                                        <span class="" v-text="forms.entity.createUpdate.extras.modals.details.data.extras.formatted_duration"></span>
                                                        <span class="ms-1">x</span>
                                                        <span class="ms-1" v-text="(isDefined({value: forms.entity.createUpdate.extras.modals.details.data.quantity}) ? forms.entity.createUpdate.extras.modals.details.data.quantity : '0')+' periodos'"></span>
                                                        <span class="ms-1">=</span>
                                                        <span class="fw-bold ms-1" v-text="forms.entity.createUpdate.extras.modals.details.data.extras.formatted_total_duration"></span>
                                                    </h5>
                                                </div>
                                                <!-- <div v-if="false  && ['day', 'month', 'year'].includes(forms.entity.createUpdate.extras.modals.details.data.extras?.duration_type)" class="form-check form-check-primary my-1">
                                                    <label class="form-check-label fw-semibold">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            v-model="forms.entity.createUpdate.extras.modals.details.data.extras.set_end_of_day"
                                                            @change="calculateDuration({record: forms.entity.createUpdate.extras.modals.details.data})"/>
                                                        Ajustar la <u>hora de la Fecha de finalización</u> al final del día (23:59 = 11:59 PM)
                                                    </label>
                                                </div> -->
                                                <!--<div class="form-check form-check-primary fw-semibold my-1">
                                                    <label class="form-check-label">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            v-model="forms.entity.createUpdate.extras.modals.details.data.extras.force"/>
                                                        Tomar en cuenta la membresías activas
                                                    </label>
                                                </div>-->
                                            </template>
                                        </InputSlot>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion mt-3" id="accordionSubscriptions" v-if="false && isDefined({value: forms.entity.createUpdate.data.holder?.data?.id})">
                            <div class="card accordion-item">
                                <h2 class="accordion-header d-flex align-items-center border">
                                    <button type="button" class="accordion-button fw-semibold collapsed" data-bs-toggle="collapse" data-bs-target="#accordionSubscriptions-1" aria-expanded="false">
                                        <i class="fa-solid fa-binoculars"></i>
                                        <span class="ms-2">Membresías activas</span>
                                        <span class="badge badge-center rounded-pill bg-primary ms-2" v-text="(options?.holders?.subscriptions[forms.entity.createUpdate.data.holder?.data?.id] ?? []).length"></span>
                                    </button>
                                </h2>
                                <div id="accordionSubscriptions-1" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="table-responsive my-3">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr class="text-center align-middle">
                                                        <th class="bg-secondary text-white fw-semibold col-1">#</th>
                                                        <th class="bg-secondary text-white fw-semibold text-nowrap col-2">FECHA DE INICIO</th>
                                                        <th class="bg-secondary text-white fw-semibold text-nowrap col-2">FECHA DE FINALIZACIÓN</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="table-border-bottom-0 bg-white">
                                                    <template v-if="(options?.holders?.subscriptions[forms.entity.createUpdate.data.holder?.data?.id] ?? []).length > 0">
                                                        <template v-for="(record, keyRecord) in options?.holders?.subscriptions[forms.entity.createUpdate.data.holder?.data?.id]" :key="record.id">
                                                            <tr class="text-nowrap text-center">
                                                                <td v-text="keyRecord + 1"></td>
                                                                <td>
                                                                    <span v-text="legibleFormatDate({dateString: record.start_date, type: 'date'})" class="d-block fw-semibold"></span>
                                                                    <span v-text="legibleFormatDate({dateString: record.start_date, type: 'time'})" class="d-block fw-semibold"></span>
                                                                </td>
                                                                <td>
                                                                    <span v-text="legibleFormatDate({dateString: record.end_date, type: 'date'})" class="d-block fw-semibold"></span>
                                                                    <span v-text="legibleFormatDate({dateString: record.end_date, type: 'time'})" class="d-block fw-semibold"></span>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </template>
                                                    <template v-else>
                                                        <tr>
                                                            <td class="text-center" colspan="99">
                                                                <WithoutData type="text"/>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" :class="['btn waves-effect', ['store'].includes(forms.entity.createUpdate.extras.modals.details.data?.mode) ? 'btn-primary' : 'btn-warning']" @click="addDetail()">
                        <i class="fa fa-save"></i>
                        <span class="ms-2">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms.entity.createUpdate.extras.modals.subscriptions.id" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="forms.entity.createUpdate.extras.modals.subscriptions.titles.default"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="d-block">
                            <i class="fa fa-user"></i>
                            <span class="ms-2">Cliente:</span>
                            <span v-text="forms.entity.createUpdate.data.holder?.data?.document_number" class="fw-bold ms-2"></span>
                            <span class="fw-bold ms-1">-</span>
                            <span v-text="forms.entity.createUpdate.data.holder?.data?.name" class="fw-bold ms-1"></span>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr class="text-center align-middle">
                                        <th class="bg-secondary text-white fw-semibold col-1">#</th>
                                        <th class="bg-secondary text-white fw-semibold text-nowrap col-2">FECHA DE INICIO</th>
                                        <th class="bg-secondary text-white fw-semibold text-nowrap col-2">FECHA DE FINALIZACIÓN</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0 bg-white">
                                    <template v-if="forms.entity.createUpdate.extras.modals.subscriptions.data.loading">
                                        <tr class="text-center">
                                            <td colspan="99" class="py-4">
                                                <Loader/>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <template v-if="(options?.holders?.subscriptions[forms.entity.createUpdate.data.holder?.data?.id] ?? []).length > 0">
                                            <template v-for="(record, keyRecord) in options?.holders?.subscriptions[forms.entity.createUpdate.data.holder?.data?.id]" :key="record.id">
                                                <tr class="text-nowrap text-center">
                                                    <td v-text="keyRecord + 1"></td>
                                                    <td>
                                                        <span v-text="legibleFormatDate({dateString: record.start_date, type: 'date'})" class="d-block fw-semibold"></span>
                                                        <span v-text="legibleFormatDate({dateString: record.start_date, type: 'time'})" class="d-block fw-semibold"></span>
                                                    </td>
                                                    <td>
                                                        <span v-text="legibleFormatDate({dateString: record.end_date, type: 'date'})" class="d-block fw-semibold"></span>
                                                        <span v-text="legibleFormatDate({dateString: record.end_date, type: 'time'})" class="d-block fw-semibold"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td class="text-center" colspan="99">
                                                    <WithoutData type="text"/>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" :class="['btn waves-effect', 'btn-info-1']" @click="refreshSubscriptions()">
                        <i class="fa fa-refresh"></i>
                        <span class="ms-2">Actualizar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <PrintSale :modalId="forms.entity.createUpdate.extras.modals.finished.id" :data="forms.entity.createUpdate.extras.modals.finished.data">
        <template v-slot:messageAppend>
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 text-center mb-2">
                <template v-if="forms.entity.createUpdate.extras.modals.finished.data?.extras?.bool">
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle text-success fs-4"></i>
                        <span class="fw-semibold fs-5 ms-2" v-text="forms.entity.createUpdate.extras.modals.finished.data?.extras?.msg"></span>
                    </div>
                </template>
                <template v-else>
                    <div class="alert alert-danger">
                        <i class="fa fa-check-circle text-danger fs-4"></i>
                        <span class="fw-semibold fs-5 ms-2" v-text="forms.entity.createUpdate.extras.modals.finished.data?.extras?.msg"></span>
                    </div>
                </template>
            </div>
        </template>
        <template v-slot:extraGroupAppend>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mx-2">
                <div class="text-center cursor-pointer" data-bs-dismiss="modal">
                    <div class="badge bg-success p-3 rounded mb-1">
                        <i class="fa-solid fa-cash-register fs-3"></i>
                    </div>
                    <span class="d-block fw-semibold">Nueva venta</span>
                </div>
            </div>
            <div class="row g-2 mt-4">
                <InputText
                    hasDiv
                    title="Número de celular (Whatsapp)"
                    v-model="forms.entity.createUpdate.extras.modals.finished.data.whatsapp">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-success waves-effect" type="button" @click="sendWhatsapp({data: forms.entity.createUpdate.extras.modals.finished.data})" :disabled="!isDefined({value: forms.entity.createUpdate.extras.modals.finished.data.whatsapp})">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span class="ms-2">Enviar</span>
                        </button>
                    </template>
                </InputText>
                <InputText
                    v-if="false"
                    hasDiv
                    title="Correo electrónico"
                    v-model="forms.entity.createUpdate.extras.modals.finished.data.email">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-info-1 waves-effect" type="button" @click="sendEmail({data: forms.entity.createUpdate.extras.modals.finished.data})" :disabled="!isDefined({value: forms.entity.createUpdate.extras.modals.finished.data.email})">
                            <i class="fa fa-envelope"></i>
                            <span class="ms-2">Enviar</span>
                        </button>
                    </template>
                </InputText>
            </div>
        </template>
    </PrintSale>
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

        Utils.navbarItem("menu-item-sales", {addClass: "open"});
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
                                details: {
                                    id: Utils.uuid(),
                                    titles: {
                                        store: "Agregar",
                                        update: "Editar"
                                    },
                                    data: {
                                        id: null,
                                        item: null,
                                        type: "",
                                        currency: null,
                                        name: "",
                                        quantity: 1,
                                        price: 0,
                                        observation: "",
                                        extras: {
                                            min_price: "",
                                            max_price: "",
                                            duration_type: "",
                                            duration_value: "",
                                            start_date: "",
                                            end_date: "",
                                            set_end_of_day: false,
                                            force: false,
                                            observation: "",
                                            formatted_duration: "",
                                            formatted_total_duration: "",
                                            formatted_type: "",
                                            showDetail: true
                                        },
                                        mode: "store"
                                    },
                                    errors: {}
                                },
                                subscriptions: {
                                    id: Utils.uuid(),
                                    titles: {
                                        default: "Membresías activas"
                                    },
                                    data: {
                                        loading: false
                                    }
                                },
                                finished: {
                                    id: Utils.uuid(),
                                    data: {
                                        id: null,
                                        extras: {},
                                        whatsapp: "",
                                        email: ""
                                    }
                                }
                            }
                        },
                        data: {
                            id: null,
                            branch: null,
                            serie: null,
                            issue_date: "",
                            holder: null,
                            currency: null,
                            observation: "",
                            status: "",
                            details: []
                        },
                        errors: {
                            details: []
                        }
                    }
                }
            },
            options: {},
            config: {
                ...Constants.generalConfig,
                entity: {
                    ...Requests.config({entity: "sales"}),
                    page: {
                        title: "Nuevo",
                        active: true,
                        menu: {
                            id: "menu-item-sales-create"
                        }
                    }
                }
            }
        };
    },
    methods: {
        // Init
        async initParams({}) {

            const initParams = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "main"}, showAlert: true});

            this.options.branches    = initParams.data?.config?.branches;
            this.options.currencies  = initParams.data?.config?.currencies;
            this.options.holders     = {subscriptions: {}, ...initParams.data?.config?.customers};
            this.options.identityDocumentTypes = initParams.data?.config?.identityDocumentTypes;
            this.options.items       = initParams.data?.config?.items;
            this.options.salesHeader = initParams.data?.config?.salesHeader;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.forms.entity.createUpdate.data.branch     = (this.branches).length > 0 ? this.branches[0] : null;
                this.forms.entity.createUpdate.data.issue_date = Utils.getCurrentDate();
                this.forms.entity.createUpdate.data.holder     = (this.holders).length > 0 ? this.holders[0] : null;
                this.forms.entity.createUpdate.data.currency   = (this.currencies).length > 0 ? this.currencies[0] : null;

                resolve(true);

            });

        },
        // Actions modal detail
        modalAddDetail({}) {

            let form = this.forms.entity.createUpdate.extras.modals.details;

            form.data.mode = "store";

            Alerts.modals({type: "show", id: form.id});

        },
        addDetail() {

            const functionName = "addDetail";

            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.extras.modals.details.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                delete form.item.data;

                if(["store"].includes(form.mode)) {

                    (this.forms.entity.createUpdate.data.details).push({...form, id: Utils.uuid()});

                    Alerts.toastrs({type: "success", subtitle: `Se ha agregado <b><small>(${form?.quantity})</small> ${form?.name}</b> al detalle de la venta.`});

                    this.clearForm({functionName});

                }

            }else {

                // this.formErrors({functionName, type: "set", errors: validateForm});
                // Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`});

            }

        },
        changeQuantityDetail({record, keyRecord, type = "add"}) {

            let operation = 0;

            const quantity = record?.quantity ?? 0;

            if(["add"].includes(type)) {

                operation = Number(quantity) + 1;

            }else if(["subtract"].includes(type)) {

                operation = Number(quantity) - 1;

            }

            if(Number(operation) >= 0) {

                record.quantity = operation;

                this.calculateDuration({record});

            }else {

                Alerts.toastrs({type: "error", subtitle: this.config.forms.errors.labels.min_number_0});

            }

        },
        deleteDetail({record, keyRecord}) {

            const functionName = "deleteDetail";

            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(record);

            const validateForm = this.validateForm({functionName, form});

            if(validateForm?.bool) {

                let el = this;

                Swal.fire({
                    html: `<span>¿Desea eliminar <b>${form?.name}</b> del detalle de la venta?</span>`,
                    icon: "warning",
                    allowOutsideClick: false,
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    customClass: {
                        confirmButton: "btn btn-danger waves-effect",
                        cancelButton: "btn btn-secondary waves-effect ms-3"
                    }
                })
                .then(function(result) {

                    if(result.isConfirmed) {

                        (el.forms.entity.createUpdate.data.details).splice(keyRecord, 1);

                        Alerts.toastrs({type: "success", subtitle: `<b>${form?.name}</b> ha sido eliminado del detalle de la venta.`});

                    }else if(result.isDismissed) {

                        //

                    }

                });

            }

        },
        duplicateDetail({record, keyRecord}) {

            const functionName = "duplicateDetail";

            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(record);

            const validateForm = this.validateForm({functionName, form});

            if(validateForm?.bool) {

                let el = this;

                Swal.fire({
                    html: `<span>¿Desea duplicar <b>${form?.name}</b> al detalle de la venta?</span>`,
                    icon: "question",
                    allowOutsideClick: false,
                    showCancelButton: true,
                    confirmButtonText: "Sí, duplicar",
                    cancelButtonText: "Cancelar",
                    customClass: {
                        confirmButton: "btn btn-info-1 waves-effect",
                        cancelButton: "btn btn-secondary waves-effect ms-3"
                    }
                })
                .then(function(result) {

                    if(result.isConfirmed) {

                        form.id = Utils.uuid();

                        (el.forms.entity.createUpdate.data.details).push(form);

                        Alerts.toastrs({type: "success", subtitle: `<b>${form?.name}</b> ha sido agregado al detalle de la venta.`});

                    }else if(result.isDismissed) {

                        //

                    }

                });

            }

        },
        viewDetail({record, keyRecord}) {

            record.extras.showDetail = !record.extras.showDetail;

        },
        viewSubscriptions({}) {

            let form = this.forms.entity.createUpdate.extras.modals.subscriptions;

            this.refreshSubscriptions();

            Alerts.modals({type: "show", id: form.id});

        },
        async refreshSubscriptions() {

            let form = this.forms.entity.createUpdate.extras.modals.subscriptions;

            form.data.loading = true;

            const getSubscriptions = await Utils.getSubscriptions({customer: this.forms.entity.createUpdate.data.holder?.data});

            this.options.holders.subscriptions[this.forms.entity.createUpdate.data.holder?.data?.id] = Requests.valid({result: getSubscriptions}) ? getSubscriptions?.data?.subscriptions : false;

            form.data.loading = false;

        },
        // Entity forms
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms.entity.createUpdate.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                form.branch_id   = form?.branch?.code;
                form.serie_id    = form?.serie?.code;
                form.holder_id   = form?.holder?.code;
                form.currency_id = form?.currency?.code;

                delete form.branch;
                delete form.serie;
                delete form.holder;
                delete form.currency;

                form.details.forEach(detail => {

                    detail.item_id = detail?.item?.code;
                    detail.currency_id = detail?.currency?.id;

                    delete detail.item;
                    delete detail.currency;

                });

                const createUpdate = await (this.isDefined({value: form.id}) ? Requests.patch({route: this.config.entity.routes.update, data: form, id: form.id}) :
                                                                               Requests.post({route: this.config.entity.routes.store, data: form}));

                if(Requests.valid({result: createUpdate})) {

                    const {sale, ...extras} = createUpdate.data;

                    let holder = this.forms.entity.createUpdate.data.holder;

                    const whatsapp = this.isDefined({value: holder.data?.phone_number}) ? holder.data?.phone_number : ""; // this.forms.entity.createUpdate.extras.modals.finished.data.whatsapp;
                    const email    = this.isDefined({value: holder.data?.email}) ? holder.data?.email : ""; // this.forms.entity.createUpdate.extras.modals.finished.data.email;

                    this.forms.entity.createUpdate.extras.modals.finished.data = {...sale, extras, whatsapp, email};

                    Alerts.swals({show: false});
                    Alerts.modals({type: "show", id: this.forms.entity.createUpdate.extras.modals.finished.id, timeout: 300});

                    this.clearForm({functionName});

                }else {

                    this.formErrors({functionName, type: "set", errors: createUpdate?.errors ?? []});
                    Alerts.toastrs({type: "error", subtitle: createUpdate?.data?.msg});
                    Alerts.swals({show: false});

                }

            }else {

                // this.formErrors({functionName, type: "set", errors: validateForm});
                // Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                // Alerts.swals({show: false});
                Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`});

            }

        },
        // Forms utils
        addCustomerPostAction({response = nul}) {

            if(Requests.valid({result: response}) && this.isDefined({value: response?.data?.customer})) {

                (this.options.holders.records).push(response?.data?.customer);

            }

        },
        clearForm({functionName}) {

            switch(functionName) {
                case "addDetail":
                    this.forms.entity.createUpdate.extras.modals.details.data.id   = null;
                    this.forms.entity.createUpdate.extras.modals.details.data.item = null;
                    break;

                case "createUpdateEntity":
                    this.forms.entity.createUpdate.data.id          = null;
                    // this.forms.entity.createUpdate.data.issue_date  = Utils.getCurrentDate();
                    // this.forms.entity.createUpdate.data.holder      = null;
                    this.forms.entity.createUpdate.data.observation = "";
                    this.forms.entity.createUpdate.data.status      = "";
                    // this.forms.entity.createUpdate.data.details     = [];
                    break;
            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(["addDetail"].includes(functionName)) {

                this.forms.entity.createUpdate.extras.modals.details.errors = ["set"].includes(type) ? errors : [];

            }else if(["createUpdateEntity"].includes(functionName)) {

                this.forms.entity.createUpdate.errors = ["set"].includes(type) ? errors : [];

            }

        },
        validateForm({functionName, form = null, extras = null}) {

            let result = {
                bool: true
            };

            if(["addDetail"].includes(functionName)) {

                result.item       = [];
                result.quantity   = [];
                result.price      = [];
                result.extras_start_date = [];
                result.extras_end_date   = [];

                const isDescriptive = ["descriptive"].includes(extras?.type);

                if(!this.isDefined({value: form?.item})) {

                    result.item.push(`${isDescriptive ? "Catálogo comercial:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }else {

                    if(this.isSubscription(form?.type)) {

                        if(!this.isDefined({value: form?.extras?.start_date})) {

                            result.extras_start_date.push(`${isDescriptive ? "Fecha de inicio:" : ""} ${this.config.forms.errors.labels.required}`);
                            result.bool = false;

                        }

                        if(!this.isDefined({value: form?.extras?.end_date})) {

                            result.extras_end_date.push(`${isDescriptive ? "Fecha de finalización:" : ""} ${this.config.forms.errors.labels.required}`);
                            result.bool = false;

                        }else {

                            /* let today = new Date();
                            let endDate = new Date(form?.extras?.end_date);

                            today.setHours(0, 0, 0, 0);
                            endDate.setHours(0, 0, 0, 0);

                            if(today > endDate) {

                                result.extras_end_date.push(`${isDescriptive ? "Fecha de finalización:" : ""} Debe ser mayor a la fecha de hoy.`);
                                result.bool = false;

                            } */

                        }

                        /* if(!form?.extras?.force) {

                            const subscriptions = Utils.cloneJson(this.options?.holders?.subscriptions[this.forms.entity.createUpdate.data.holder?.data?.id]);

                            let findOverlaps = Utils.findOverlaps({start_date: form?.extras?.start_date, end_date: form?.extras?.end_date}, subscriptions);

                            if(findOverlaps.hasOverlap) {

                                let messages = findOverlaps.positions.map(e =>
                                    `<div class="mt-3">
                                        <span class="d-block fw-bold">Membresía activa #${parseInt(e?.keyArray) + 1}</span>
                                        <div class="d-block mt-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="fw-semibold ms-1">F. inicio:</span>
                                            <span class="badge bg-label-primary fw-bold">${this.legibleFormatDate({dateString: e?.start_date})}</span>
                                        </div>
                                        <div class="d-block mt-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="fw-semibold ms-1">F. final.:</span>
                                            <span class="badge bg-label-success fw-bold">${this.legibleFormatDate({dateString: e?.end_date})}</span>
                                        </div>
                                    </div>`
                                );

                                result.extras_end_date = [`El rango de fechas ingresadas se cruzan con las siguientes MEMBRESÍAS ACTIVAS: `+messages.join("")];
                                result.bool = false;

                            }

                        } */

                    }

                }

                if(!this.isDefined({value: form?.quantity}) || Number(form?.quantity) <= 0) {

                    result.quantity.push(`${isDescriptive ? "Cantidad:" : ""} ${this.config.forms.errors.labels.min_number_0}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.price}) || Number(form?.price) <= 0) {

                    result.price.push(`${isDescriptive ? "Precio:" : ""} ${this.config.forms.errors.labels.min_number_0}`);
                    result.bool = false;

                }

            }else if(["deleteDetail"].includes(functionName)) {

                result.item = [];

                if(!this.isDefined({value: form?.item})) {

                    result.item.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

            }else if(["duplicateDetail"].includes(functionName)) {

                result.item = [];

                if(!this.isDefined({value: form?.item})) {

                    result.item.push(this.config.forms.errors.labels.required);
                    result.bool = false;

                }

            }else if(["createUpdateEntity"].includes(functionName)) {

                result.branch      = [];
                result.serie       = [];
                result.issue_date  = [];
                result.holder      = [];
                result.currency    = [];
                result.observation = [];
                result.status      = [];
                result.details     = [];

                const isDescriptive = ["descriptive"].includes(extras?.type);

                if(!this.isDefined({value: form?.branch})) {

                    result.branch.push(`${isDescriptive ? "Sucursal:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.serie})) {

                    result.serie.push(`${isDescriptive ? "Tipo de comprobante:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.issue_date})) {

                    result.issue_date.push(`${isDescriptive ? "Fecha de emisión:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(!this.isDefined({value: form?.holder})) {

                    result.holder.push(`${isDescriptive ? "Cliente:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }else {

                    if((form?.serie?.data?.document_type_id == 1 && ![1, 2].includes(form?.holder?.data?.identity_document_type_id)) ||
                       (form?.serie?.data?.document_type_id == 2 && ![4].includes(form?.holder?.data?.identity_document_type_id))) {

                        result.holder.push(`${isDescriptive ? "Cliente:" : ""} No apto para generar el comprobante <b>(${form?.serie?.label})</b>.`);
                        result.bool = false;

                    }

                }

                if(!this.isDefined({value: form?.currency})) {

                    result.currency.push(`${isDescriptive ? "Tipo de moneda:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }

                if(this.isDefined({value: form?.id})) {

                    if(!this.isDefined({value: form?.status})) {

                        result.status.push(`${isDescriptive ? "Estado:" : ""} ${this.config.forms.errors.labels.required}`);
                        result.bool = false;

                    }

                }

                if(!this.isDefined({value: form?.details}) || (form?.details).length === 0) {

                    result.details.push(`${isDescriptive ? "Detalle de la venta:" : ""} ${this.config.forms.errors.labels.required}`);
                    result.bool = false;

                }else {

                    let errorDetails = [];

                    for(let [keyDetail, detail] of Object.entries(form?.details)) {

                        const validateDetail = this.validateForm({functionName: "addDetail", form: detail, extras: {type: "descriptive"}});

                        if(!validateDetail?.bool) {

                            let propsValidate = Utils.getErrors({errors: validateDetail});

                            errorDetails.push(`<p class="mb-1">Detalle de la venta <b>#${parseInt(keyDetail) + 1}</b>:</p>`+propsValidate.flat().map(e => `<li>${e}</li>`).join(""));
                            result.bool = false;

                        }

                    }

                    result.details = [errorDetails.join("<br/>")];

                }

            }

            return result;

        },
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        },
        isSubscription(type) {

            return ["subscription"].includes(type);

        },
        calculateTotal({item}) {

            return Utils.calculateTotal({item});

        },
        calculateDuration({mode = "record", record = null}) {

            let data = record;

            if(this.isSubscription(data?.type)) {

                let setEndOfDay   = data?.extras?.set_end_of_day,
                    startDate     = data?.extras?.start_date,
                    durationType  = data?.extras?.duration_type,
                    durationValue = Number(data?.extras?.duration_value),
                    quantity      = Number(data.quantity);

                let durationTotal = isNaN(durationValue) || isNaN(quantity) ? 0 : (durationValue * quantity);

                const endDate = Utils.addDuration({startDate, type: durationType, quantity: durationTotal, setEndOfDay});

                const durationTypeLegible = this.options.items.durationTypes.filter(e => e.code === durationType)[0];

                if(["record"].includes(mode)) {

                    record.extras.end_date = endDate;

                    record.extras.formatted_total_duration = `${durationTotal} `+(durationTotal > 1 ? durationTypeLegible?.plural : durationTypeLegible?.label);

                }else if(["result"].includes(mode)) {

                    return endDate;

                }

            }

        },
        getItemDecimals({mode = "record", record = null}) {

            const decimals = this.isSubscription(record?.type) ? 0 : this.config.forms.inputs.round;

            if(["record"].includes(mode)) {

                record.decimals = decimals;

            }else if(["result"].includes(mode)) {

                return decimals;

            }

        },
        fixedNumber(value, decimals = null) {

            return Utils.fixedNumber(value, decimals);

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        },
        legibleFormatDate({dateString = null, type = "datetime"}) {

            return Utils.legibleFormatDate({dateString, type});

        },
        sendWhatsapp({data = null, action = "reportSale"}) {

            const phoneNumber = this.forms.entity.createUpdate.extras.modals.finished.data.whatsapp;
            const message     = Utils.getMessageWhatsapp({data, action});

            Utils.sendWhatsapp({phoneNumber, message});

        },
        async sendEmail({data = null, action = "reportSale"}) {

            let route = Requests.config({entity: "helpers", type: "sendEmail"});
            const formJson = {serie_sequential: data?.serie_sequential, email: data?.email, message: Utils.getMessageWhatsapp({data, action})};

            Alerts.swals({});

            let sendEmail = await Requests.post({route: route, data: formJson, id: data?.id});

            if(Requests.valid({result: sendEmail})) {

                Alerts.toastrs({type: "success", subtitle: sendEmail?.data?.msg});
                Alerts.swals({show: false});

            }else {

                Alerts.toastrs({type: "error", subtitle: sendEmail?.data?.msg});
                Alerts.swals({show: false});

            }

            Alerts.tooltips({show: false});

        }
    },
    computed: {
        breadcrumbTitles: function() {

            return [{title: "Ventas"}, this.config.entity.page];

        },
        branches: function() {

            return this.options?.branches?.records.map(e => ({code: e.id, label: e.name, data: e}));

        },
        series: function() {

            const branch = (this.options?.branches?.records ?? []).filter(e => e?.id == this.forms.entity.createUpdate.data.branch?.code);

            if(branch.length === 1) {

                const series = branch[0].series;

                return series.map(e => ({code: e.id, label: `${e.legible_serie} - ${e?.document_type?.name}`, data: e}));

            }

            return [];

        },
        holders: function() {

            return this.options?.holders?.records.map(e => ({code: e.id, label: `${e.document_number} - ${e.name}`, data: e}));

        },
        currencies: function() {

            return this.options?.currencies?.records.map(e => ({code: e.id, label: e.plural_name, data: e}));

        },
        items: function() {

            return this.options?.items?.records.map(e => ({code: e.id, label: e.name, data: e}));

        },
        total: function() {

            let total = 0;

            for(let detail of this.forms.entity.createUpdate.data.details) {

                total += Number(this.calculateTotal({item: detail}));

            }

            return this.fixedNumber(total);

        },
        totalModalDetail: function() {

            return this.calculateTotal({item: this.forms.entity.createUpdate.extras.modals.details.data});

        }
    },
    watch: {
        "forms.entity.createUpdate.data.branch": function(newValue, oldValue) {

            this.forms.entity.createUpdate.data.serie = (this.series).length > 0 ? this.series[0] : null;

        },
        "forms.entity.createUpdate.data.holder": async function(newValue, oldValue) {

            const getSubscriptions = await Utils.getSubscriptions({customer: newValue?.data});

            this.options.holders.subscriptions[newValue?.data?.id] = Requests.valid({result: getSubscriptions}) ? getSubscriptions?.data?.subscriptions : false;

        },
        "forms.entity.createUpdate.extras.modals.details.data.item": function(newValue, oldValue) {

            const data = newValue?.data;

            let modalData = this.forms.entity.createUpdate.extras.modals.details.data;

            modalData.type     = data?.type;
            modalData.currency = data?.currency;
            modalData.name     = data?.name;
            modalData.price    = Number(data?.price ?? 0);

            // Set quantity decimals
            const quantity = modalData?.quantity ?? 1,
                  decimals = this.getItemDecimals({mode: "result", record: modalData});

            modalData.quantity = Number(this.fixedNumber(quantity, decimals));

            // Set extras
            let extras = {
                min_price: data?.min_price ?? "",
                max_price: data?.max_price ?? "",
                duration_type: "",
                duration_value: "",
                start_date: "",
                end_date: "",
                set_end_of_day: false,
                force: false,
                observation: "",
                formatted_duration: "",
                formatted_total_duration: "",
                formatted_type: "",
                showDetail: true
            };

            if(["product", "service"].includes(data?.type)) {

                modalData.extras = extras;

            }else if(this.isSubscription(data?.type)) {

                // Set extras
                extras = {
                    min_price: data?.min_price ?? "",
                    max_price: data?.max_price ?? "",
                    duration_type: data?.duration_type,
                    duration_value: data?.duration_value,
                    start_date: Utils.isDefined({value: modalData.extras.start_date}) ? modalData.extras.start_date : Utils.getCurrentDate("datetime"),
                    end_date: "",
                    set_end_of_day: ["today"].includes(data?.duration_type),
                    force: true,
                    observation: "",
                    formatted_duration: data?.formatted_duration,
                    formatted_total_duration: "",
                    formatted_type: data?.formatted_type,
                    showDetail: true
                };

                modalData.extras = extras;

                this.calculateDuration({record: modalData});

            }else {

                modalData.extras = extras;

            }

        }
    }
};
</script>
