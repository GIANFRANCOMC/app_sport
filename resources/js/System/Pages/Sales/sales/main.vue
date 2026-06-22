<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="row g-4">
        <div class="col-lg-9 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.branch"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.branch_id"
                            xl="4"
                            lg="4">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.data.branch"
                                    :options="branches"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.serie"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.serie_id"
                            xl="4"
                            lg="4">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.data.serie"
                                    :options="series"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.warehouse"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.warehouse"
                            xl="4"
                            lg="4">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.data.warehouse"
                                    :options="warehouses"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="false"
                                    placeholder="Seleccione"/>
                            </template>
                        </InputSlot>
                        <InputDate
                            v-model="forms[entity].createUpdate.data.issue_date"
                            hasDiv
                            :title="MODULE.texts.form.issueDate"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            disabled
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.issue_date"
                            xl="3"
                            lg="6"/>
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.holder"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.holder_id"
                            xl="9"
                            lg="12">
                            <template v-slot:default>
                                <AddCustomer
                                    :options="customerOptions"
                                    @postAction="addCustomerPostAction"/>
                            </template>
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.data.holder"
                                    :options="holders"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="true"
                                    placeholder="Seleccione"/>
                            </template>
                        </InputSlot>
                    </div>
                    <div class="row g-3">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width: 10%;">#</th>
                                        <th class="min-w-150px" style="width: 20%;">DESCRIPCIÓN</th>
                                        <th class="min-w-150px" style="width: 20%;">CANTIDAD</th>
                                        <th class="min-w-150px" style="width: 20%;">PRECIO UNITARIO</th>
                                        <th class="min-w-150px text-end pe-3" style="width: 20%;">TOTAL</th>
                                        <th style="width: 10%;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0 bg-white">
                                    <template v-if="(forms[entity].createUpdate.data.details).length > 0">
                                        <template v-for="(record, keyRecord) in forms[entity].createUpdate.data.details" :key="record.id">
                                            <tr>
                                                <td class="text-center fw-bold" :rowspan="isSubscription(record?.type) && record?.extras?.showDetail ? 2 : 1">
                                                    <span v-text="keyRecord + 1"></span>
                                                </td>
                                                <td class="text-start">
                                                    <span class="text-break" v-text="record.name"></span>
                                                </td>
                                                <td class="text-center">
                                                    <InputNumber
                                                        v-model="record.quantity"
                                                        @change="calculateDuration({mode: 'record', record})"
                                                        :decimals="getItemDecimals({mode: 'result', record})"/>
                                                    <div class="d-flex justify-content-center gap-2 mt-1">
                                                        <button class="btn btn-danger btn-xs waves-effect" type="button" @click="changeQuantityDetail({record, keyRecord, type: 'subtract'})">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                        <button class="btn btn-info-1 btn-xs waves-effect" type="button" @click="changeQuantityDetail({record, keyRecord, type: 'add'})">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <InputNumber v-model="record.price">
                                                        <template v-slot:inputGroupPrepend v-if="isDefined({value: record?.currency})">
                                                            <div class="input-group-text br-input-currency-addon">
                                                                <span class="br-input-currency-addon__sign" v-text="record?.currency?.sign"></span>
                                                            </div>
                                                        </template>
                                                    </InputNumber>
                                                </td>
                                                <td class="text-end align-middle pe-3" :title="MODULE.texts.form.total">
                                                    <span class="br-amount-inline">
                                                        <span class="br-amount-inline__sign" v-text="record.currency?.sign ?? ''"></span>
                                                        <span class="br-amount-inline__amount" v-text="separatorNumber(calculateTotal({item: record}))"></span>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <InputSlot
                                                        hasDiv
                                                        :isInputGroup="false"
                                                        :divInputClass="['d-flex flex-wrap justify-content-center gap-2 gap-md-1']"
                                                        xl="12"
                                                        lg="12">
                                                        <template v-slot:input>
                                                            <button class="btn btn-danger btn-xs waves-effect" type="button" @click="deleteDetail({record, keyRecord})">
                                                                <i class="fa fa-times"></i>
                                                                <span class="ms-1" v-text="MODULE.texts.actions.delete"></span>
                                                            </button>
                                                            <button v-if="!isSubscription(record?.type)" class="btn btn-info-1 btn-xs waves-effect" type="button" @click="duplicateDetail({record, keyRecord})">
                                                                <i class="fa fa-copy"></i>
                                                                <span class="ms-1" v-text="MODULE.texts.actions.duplicate"></span>
                                                            </button>
                                                            <template v-if="isSubscription(record?.type)">
                                                                <button class="btn btn-success btn-xs waves-effect" type="button" @click="viewDetail({record, keyRecord})">
                                                                    <i :class="record?.extras?.showDetail ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                                                                    <span class="ms-1" v-text="record?.extras?.showDetail ? 'Detalle' : 'Detalle'"></span>
                                                                </button>
                                                            </template>
                                                        </template>
                                                    </InputSlot>
                                                </td>
                                            </tr>
                                            <template v-if="record?.extras?.showDetail">
                                                <template v-if="isSubscription(record?.type)">
                                                    <tr>
                                                        <td class="text-center align-middle br-table-expand-label br-table-expand-label--black-text colon-at-end" v-text="MODULE.texts.form.detailRowMembershipLabel"></td>
                                                        <td colspan="5">
                                                            <div class="row g-3 pt-3 pb-4 px-4">
                                                                <div class="col-6">
                                                                    <InputDatetime
                                                                        title="Fecha de inicio"
                                                                        v-model="record.extras.start_date"
                                                                        @change="calculateDuration({mode: 'record', record})"
                                                                        isRequired/>
                                                                </div>
                                                                <div class="col-6">
                                                                    <InputDatetime
                                                                        title="Fecha de finalización"
                                                                        v-model="record.extras.end_date"
                                                                        isRequired
                                                                        disabled/>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </template>
                                        </template>
                                        <tr class="br-table-footer-stripe">
                                            <td colspan="4" class="text-end align-middle br-table-footer-stripe__label">
                                                <span v-text="MODULE.texts.form.footerTotalLabel" class="text-uppercase"></span>
                                            </td>
                                            <td class="text-end align-middle pe-3">
                                                <span class="br-amount-inline br-amount-inline--emphasis">
                                                    <span class="br-amount-inline__sign" v-text="forms[entity].createUpdate.data.currency?.data?.sign ?? ''"></span>
                                                    <span class="br-amount-inline__amount" v-text="separatorNumber(total)"></span>
                                                </span>
                                            </td>
                                            <td class="align-middle"></td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td class="pt-1 pb-0 border-0" colspan="99">
                                                <div class="br-table-detail-empty">
                                                    <div class="br-table-detail-empty__top">
                                                        <div class="br-table-detail-empty__body">
                                                            <img
                                                                class="br-table-detail-empty__img"
                                                                :src="saleDetailEmptyImageUrl"
                                                                alt=""
                                                                width="100"
                                                                height="84"
                                                                loading="lazy"
                                                                decoding="async"/>
                                                            <p class="br-table-detail-empty__text">
                                                                <span v-text="MODULE.texts.emptySaleDetailPrefix"></span>
                                                                <a href="javascript:void(0)" class="br-link br-table-detail-empty__link" @click.prevent="modalAddDetail({})" v-text="MODULE.texts.actions.addDetail"></a>
                                                                <span v-text="MODULE.texts.emptySaleDetailSuffix"></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <!-- <small :class="config.forms.errors.styles.default" v-html="isDefined({value: forms[entity].createUpdate.errors?.details}) ? forms[entity].createUpdate.errors?.details : ''"></small> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-12">
            <div class="w-100 mb-2 mb-md-3">
                <div class="br-observation-tile">
                    <div
                        role="button"
                        tabindex="0"
                        class="br-tap-field"
                        :class="observationHasContent ? 'br-tap-field--has-value' : 'br-tap-field--empty'"
                        @click="openObservationsModal"
                        @keydown.enter.prevent="openObservationsModal"
                        @keydown.space.prevent="openObservationsModal"
                        :aria-label="observationsFieldAriaLabel">
                        <div class="br-tap-field__head">
                            <span class="br-tap-field__eyebrow" v-text="MODULE.texts.form.observation"></span>
                            <i class="br-tap-field__icon" :class="observationHasContent ? 'fa-solid fa-square-pen' : 'fa-solid fa-note-sticky'" aria-hidden="true"></i>
                        </div>
                        <span
                            v-if="observationHasContent"
                            class="br-tap-field__value"
                            :class="{ 'br-tap-field__value--expanded': observationPreviewExpanded }"
                            :title="observationPreviewTooltip"
                            v-text="observationDisplayPreview">
                        </span>
                        <span v-else class="br-tap-field__placeholder" v-text="MODULE.texts.observations.discreteEmpty"></span>
                    </div>
                    <button
                        v-if="observationHasContent && observationIsTruncatable"
                        type="button"
                        class="br-observation-tile__toggle"
                        :aria-expanded="observationPreviewExpanded"
                        @click.stop="toggleObservationPreviewExpand">
                        <span v-text="observationPreviewToggleLabel"></span>
                    </button>
                </div>
                <p
                    v-if="forms[entity].createUpdate.errors?.observation?.length"
                    class="small mb-0 mt-2"
                    :class="config.forms.errors.styles.default"
                    v-html="observationErrorsDisplay"></p>
            </div>
            <div class="br-document-settlement br-sale-settlement mb-2 mb-md-3">
                <div>
                    <label class="form-label">Impuestos extras</label>
                    <div class="br-document-settlement__taxes" v-if="optionalSaleTaxes.length">
                        <template v-for="tax in optionalSaleTaxes" :key="`optional-sale-tax-${tax.code}`">
                            <label class="br-entity-switch br-document-settlement__tax-option">
                                <input
                                    v-model="forms[entity].createUpdate.data.selected_taxes"
                                    class="form-check-input"
                                    type="checkbox"
                                    :value="tax.code"
                                    @change="syncSelectedTaxQuantity(tax.data)">
                                <span>
                                    <strong>{{ tax.data.name }}</strong>
                                    <small>{{ taxLabel(tax.data) }}</small>
                                </span>
                            </label>
                            <InputNumber
                                v-if="isFixedTax(tax.data) && selectedTaxIds().includes(tax.code)"
                                v-model="forms[entity].createUpdate.data.selected_tax_quantities[tax.code]"
                                title=""
                                :inputClass="['form-control', 'br-tax-quantity']"
                                :decimals="0"
                                :minValue="1"
                                :hasNegative="false"
                                @change="normalizeSelectedTaxQuantity(tax.code)">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text br-tax-quantity__label">Veces</span>
                                </template>
                            </InputNumber>
                        </template>
                    </div>
                    <p v-else class="br-document-settlement__empty mb-0">Sin impuestos extras disponibles.</p>
                </div>
                <div>
                    <label class="form-label">Métodos de pago</label>
                    <div class="br-document-payments">
                        <div
                            v-for="(payment, index) in forms[entity].createUpdate.data.payments"
                            :key="payment.key"
                            class="br-document-payment-row">
                            <v-select
                                v-model="payment.method"
                                :options="salePaymentMethods"
                                :clearable="false"
                                :searchable="true"
                                append-to-body/>
                            <InputNumber
                                v-model="payment.amount"
                                title=""
                                :titleClass="[]"
                                :inputClass="['form-control', 'br-document-payment-amount']"
                                :minValue="0"
                                :placeholder="separatorNumber(total)">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text br-currency-prefix">{{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}</span>
                                </template>
                            </InputNumber>
                            <input
                                v-if="payment.method?.data?.requires_reference"
                                v-model.trim="payment.reference"
                                type="text"
                                class="form-control"
                                maxlength="100"
                                placeholder="Referencia">
                            <button
                                type="button"
                                class="br-icon-action"
                                :disabled="forms[entity].createUpdate.data.payments.length <= 1"
                                title="Quitar método"
                                @click="removeSalePayment(index)">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>
                        <button type="button" class="br-document-payment-add" @click="addSalePayment">
                            <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                            <span>Agregar método</span>
                        </button>
                    </div>
                </div>
                <div class="br-document-settlement__summary">
                    <span>Subtotal</span>
                    <strong>
                        {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                        {{ separatorNumber(saleSubtotal) }}
                    </strong>
                    <template v-if="saleTaxBreakdown.length">
                        <template v-for="tax in saleTaxBreakdown" :key="`sale-summary-tax-${tax.id}`">
                            <span>{{ tax.name }}</span>
                            <strong>
                                {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                                {{ separatorNumber(tax.amount) }}
                            </strong>
                        </template>
                    </template>
                    <template v-else>
                        <span>IGV</span>
                        <strong>
                            {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                            0.00
                        </strong>
                    </template>
                    <span>Total</span>
                    <strong>
                        {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                        {{ separatorNumber(total) }}
                    </strong>
                    <span>Pagado</span>
                    <strong>
                        {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                        {{ separatorNumber(salePaidTotal) }}
                    </strong>
                    <span>Diferencia</span>
                    <strong :class="Number(salePaymentDifference) === 0 ? 'text-success' : 'text-danger'">
                        {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                        {{ separatorNumber(salePaymentDifference) }}
                    </strong>
                </div>
            </div>
            <div class="br-sale-sidebar-actions">
                <div class="br-sale-sidebar-actions__pair">
                    <button type="button" class="br-btn br-btn-sm br-btn-primary waves-effect" @click="modalAddDetail({})">
                        <span v-text="MODULE.texts.actions.addDetail"></span>
                    </button>
                    <button type="button" class="br-btn br-btn-sm br-btn-secondary waves-effect" @click="viewSubscriptions({})">
                        <span v-text="MODULE.texts.actions.viewMemberships"></span>
                    </button>
                </div>
                <button type="button" class="br-btn br-btn-success br-sale-sidebar-actions__cta waves-effect" @click="createUpdateEntity()">
                    <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
                    <span v-text="MODULE.texts.actions.generateSale"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.details.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header br-modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="modalDetailsTitle"></h5>
                    <button type="button" class="btn-header-modal" data-bs-dismiss="modal">
                        <i class="fa fa-times icon-close-modal"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.commercialCatalog"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.extras.modals.details.errors?.item_id">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.extras.modals.details.data.item"
                                    :options="items"
                                    :class="config.forms.classes.select2"
                                    @close="tooltips({show: true, time: 500})"
                                    :clearable="false"
                                    :searchable="true"
                                    placeholder="Seleccione">
                                    <template #option="{ label, data }">
                                        <div class="pb-1">
                                            <span v-text="label" class="d-block fw-bold"></span>
                                            <div class="d-block">
                                                <i class="fa fa-money-bill text-success small"></i>
                                                <small class="ms-2 colon-at-end">Precio unitario</small>
                                                <small v-text="data?.currency?.sign+' '+separatorNumber(data?.price)" class="ms-2 fw-bold"></small>
                                            </div>
                                            <div class="d-block" v-if="isDefined({value: data?.min_price}) || isDefined({value: data?.max_price})">
                                                <i class="fa fa-arrows-alt-h text-warning small"></i>
                                                <small class="ms-2 colon-at-end">Rango de precios</small>
                                                <small v-if="isDefined({value: data?.min_price})" v-text="'Min: '+data?.currency?.sign+' '+data?.min_price" class="ms-2 fw-bold"></small>
                                                <small v-if="isDefined({value: data?.max_price})" v-text="'Max: '+data?.currency?.sign+' '+data?.max_price" class="ms-2 fw-bold"></small>
                                            </div>
                                            <div class="d-block" v-if="isSubscription(data?.type)">
                                                <i class="fa fa-clock text-info small"></i>
                                                <small class="ms-2 colon-at-end">Duración de la {{ data?.formatted_type.toLowerCase() }}</small>
                                                <small v-text="data?.formatted_duration" class="ms-2 fw-bold text-lowercase"></small>
                                            </div>
                                        </div>
                                    </template>
                                </v-select>
                            </template>
                        </InputSlot>
                        <InputSlot
                            v-if="isDefined({value: forms[entity].createUpdate.extras.modals.details.data.extras?.min_price}) || isDefined({value: forms[entity].createUpdate.extras.modals.details.data.extras?.max_price})"
                            hasDiv
                            :isInputGroup="false"
                            :divInputClass="['d-flex flex-column flex-md-row flex-wrap justify-content-center align-items-start align-items-md-center gap-2 p-2 border rounded border-light bg-warning-subtle']"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <span class="fw-bold colon-at-end">Rango de precios</span>
                                <span v-if="isDefined({value: forms[entity].createUpdate.extras.modals.details.data.extras?.min_price})" v-text="'Min: '+forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency?.sign+' '+separatorNumber(forms[entity].createUpdate.extras.modals.details.data.extras?.min_price)" class="fw-semibold text-danger"></span>
                                <span v-if="isDefined({value: forms[entity].createUpdate.extras.modals.details.data.extras?.max_price})" v-text="'Max: '+forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency?.sign+' '+separatorNumber(forms[entity].createUpdate.extras.modals.details.data.extras?.max_price)" class="fw-semibold text-dark"></span>
                            </template>
                        </InputSlot>
                        <InputNumber
                            v-model="forms[entity].createUpdate.extras.modals.details.data.quantity"
                            @change="calculateDuration({mode: 'record', record: forms[entity].createUpdate.extras.modals.details.data})"
                            hasDiv
                            :title="isSubscription(forms[entity].createUpdate.extras.modals.details.data.type) ? MODULE.texts.form.quantityPeriods : MODULE.texts.form.quantity"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            :decimals="getItemDecimals({mode: 'result', record: forms[entity].createUpdate.extras.modals.details.data})"
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.extras.modals.details.errors?.quantity"
                            xl="4"
                            lg="4"/>
                        <InputNumber
                            v-model="forms[entity].createUpdate.extras.modals.details.data.price"
                            hasDiv
                            :title="MODULE.texts.form.price"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            :minValue="forms[entity].createUpdate.extras.modals.details.data.extras?.min_price"
                            :maxValue="forms[entity].createUpdate.extras.modals.details.data.extras?.max_price"
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.extras.modals.details.errors?.price"
                            xl="4"
                            lg="4">
                            <template v-slot:inputGroupPrepend v-if="isDefined({value: forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency})">
                                <div class="input-group-text br-input-currency-addon">
                                    <span class="br-input-currency-addon__sign" v-text="forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency?.sign"></span>
                                </div>
                            </template>
                        </InputNumber>
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.total"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            xl="4"
                            lg="4">
                            <template v-slot:inputGroupPrepend v-if="isDefined({value: forms[entity].createUpdate.data.currency?.data})">
                                <div class="input-group-text br-input-currency-addon">
                                    <span class="br-input-currency-addon__sign" v-text="forms[entity].createUpdate.data.currency?.data?.sign"></span>
                                </div>
                            </template>
                            <template v-slot:input>
                                <input class="form-control" disabled :value="separatorNumber(totalModalDetail)"/>
                            </template>
                        </InputSlot>
                        <template v-if="isSubscription(forms[entity].createUpdate.extras.modals.details.data.type)">
                            <InputDatetime
                                v-model="forms[entity].createUpdate.extras.modals.details.data.extras.start_date"
                                @change="calculateDuration({mode: 'record', record: forms[entity].createUpdate.extras.modals.details.data})"
                                hasDiv
                                :title="MODULE.texts.form.startDate"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.extras.modals.details.errors?.extras_start_date"
                                xl="4"
                                lg="4"/>
                            <InputDatetime
                                v-model="forms[entity].createUpdate.extras.modals.details.data.extras.end_date"
                                hasDiv
                                :title="MODULE.texts.form.endDate"
                                :titleClass="[config.forms.classes.title]"
                                isRequired
                                disabled
                                hasTextBottom
                                :textBottomInfo="forms[entity].createUpdate.extras.modals.details.errors?.extras_end_date"
                                xl="4"
                                lg="4"/>
                            <InputSlot
                                :isInputGroup="false"
                                :divInputClass="['d-flex flex-wrap justify-content-start align-items-center']">
                                <template v-slot:input>
                                    <div class="d-flex flex-wrap justify-content-start align-items-center gap-1 mb-2" v-if="false &&isDurationTypeCalendar(forms[entity].createUpdate.extras.modals.details.data.extras?.duration_type)">
                                        <div class="form-check form-check-primary">
                                            <label class="form-check-label fw-semibold">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    v-model="forms[entity].createUpdate.extras.modals.details.data.extras.set_end_of_day"
                                                    @change="calculateDuration({mode: 'record', record: forms[entity].createUpdate.extras.modals.details.data})"/>
                                                Ajustar la hora de la fecha de finalización al final del día (23:59 = 11:59 PM)
                                            </label>
                                        </div>
                                        <div class="form-check form-check-primary fw-semibold">
                                            <label class="form-check-label">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    v-model="forms[entity].createUpdate.extras.modals.details.data.extras.force"/>
                                                Tomar en cuenta la membresías activas
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 p-2 mt-1 border rounded border-light bg-success-subtle w-100">
                                        <span class="fw-bold colon-at-end text-dark">Duración total calculada</span>
                                        <div class="d-flex flex-wrap justify-content-center align-items-center fw-semibold gap-1">
                                            <span class="text-lowercase" v-text="forms[entity].createUpdate.extras.modals.details.data.extras.formatted_duration"></span>
                                            <span class="">x</span>
                                            <span class="text-lowercase" v-text="isDefined({value: forms[entity].createUpdate.extras.modals.details.data.quantity}) ? separatorNumber(forms[entity].createUpdate.extras.modals.details.data.quantity) : '0'"></span>
                                            <span class="text-lowercase" v-text="Number(forms[entity].createUpdate.extras.modals.details.data.quantity) === 1 ? 'periodo' : 'periodos'"></span>
                                            <span class="">=</span>
                                            <span class="fw-bold text-dark text-lowercase" v-text="forms[entity].createUpdate.extras.modals.details.data.extras.formatted_total_duration"></span>
                                        </div>
                                    </div>
                                </template>
                            </InputSlot>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal" v-text="MODULE.texts.actions.close"></button>
                    <button type="button" class="btn waves-effect btn-primary" @click="addDetail()">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.add"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.observations.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header br-modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="MODULE.texts.modal.observations"></h5>
                    <button type="button" class="btn-header-modal" data-bs-dismiss="modal" :aria-label="MODULE.texts.actions.close">
                        <i class="fa fa-times icon-close-modal"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <InputTextArea
                        v-model="forms[entity].createUpdate.extras.modals.observations.draft"
                        hasDiv
                        :divClass="['p-0']"
                        title=""
                        :placeholder="MODULE.texts.observations.modalPlaceholder"
                        :rows="6"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal" v-text="MODULE.texts.actions.close"></button>
                    <button type="button" class="btn btn-primary waves-effect" @click="saveObservationsModal">
                        <i class="fa fa-save"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.saveObservations"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.subscriptions.id" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header br-modal-header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="MODULE.texts.modal.activeMemberships"></h5>
                    <button type="button" class="btn-header-modal" data-bs-dismiss="modal">
                        <i class="fa fa-times icon-close-modal"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div class="d-block">
                            <i class="fa fa-user"></i>
                            <span class="ms-2">Cliente:</span>
                            <span v-text="forms[entity].createUpdate.data.holder?.data?.document_number" class="fw-bold ms-2"></span>
                            <span class="fw-bold ms-1">-</span>
                            <span v-text="forms[entity].createUpdate.data.holder?.data?.name" class="fw-bold ms-1"></span>
                        </div>
                        <button
                            type="button"
                            class="btn btn-info-1 btn-sm waves-effect"
                            @click="refreshSubscriptions()"
                            data-bs-toggle="tooltip"
                            title="Actualizar">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover br-memberships-table">
                                <thead>
                                    <tr>
                                        <th class="br-memberships-table__th br-memberships-table__th--primary">Fecha de inicio</th>
                                        <th class="br-memberships-table__th br-memberships-table__th--primary">Fecha de finalización</th>
                                        <th class="br-memberships-table__th br-memberships-table__th--meta" v-text="MODULE.texts.modal.subscriptionOrigin"></th>
                                        <th class="br-memberships-table__th br-memberships-table__th--meta" v-text="MODULE.texts.modal.subscriptionCreatedAt"></th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0 bg-white">
                                    <template v-if="forms[entity].createUpdate.extras.modals.subscriptions.data.loading">
                                        <tr class="text-center">
                                            <td colspan="99" class="py-4">
                                                <Loader/>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <template v-if="(options?.holders?.subscriptions[forms[entity].createUpdate.data.holder?.data?.id] ?? []).length > 0">
                                            <template v-for="record in options?.holders?.subscriptions[forms[entity].createUpdate.data.holder?.data?.id]" :key="record.id">
                                                <tr class="align-middle">
                                                    <td class="br-memberships-table__td">
                                                        <div class="br-memberships-table__stack">
                                                            <span class="br-memberships-table__date" v-text="legibleFormatDate({dateString: record.start_date, type: 'weekday_date'})"></span>
                                                            <span class="br-memberships-table__time br-memberships-table__time--pill" v-text="legibleFormatDate({dateString: record.start_date, type: 'time'})"></span>
                                                        </div>
                                                    </td>
                                                    <td class="br-memberships-table__td">
                                                        <div class="br-memberships-table__stack">
                                                            <span class="br-memberships-table__date" v-text="legibleFormatDate({dateString: record.end_date, type: 'weekday_date'})"></span>
                                                            <span class="br-memberships-table__time br-memberships-table__time--pill" v-text="legibleFormatDate({dateString: record.end_date, type: 'time'})"></span>
                                                        </div>
                                                    </td>
                                                    <td class="br-memberships-table__td br-memberships-table__td--meta br-memberships-table__td--center">
                                                        <span v-if="record.formatted_type" class="br-memberships-table__origin">
                                                            <i class="fa-solid fa-cash-register br-memberships-table__origin-icon" aria-hidden="true"></i>
                                                            <span class="br-memberships-table__origin-label" v-text="record.formatted_type"></span>
                                                        </span>
                                                        <span v-else class="br-memberships-table__empty">—</span>
                                                    </td>
                                                    <td class="br-memberships-table__td br-memberships-table__td--meta">
                                                        <div v-if="record.created_at" class="br-memberships-table__stack">
                                                            <span class="br-memberships-table__date" v-text="legibleFormatDate({dateString: record.created_at, type: 'weekday_date'})"></span>
                                                            <span class="br-memberships-table__time br-memberships-table__time--plain" v-text="legibleFormatDate({dateString: record.created_at, type: 'time'})"></span>
                                                        </div>
                                                        <span v-else class="br-memberships-table__empty">—</span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td class="pt-3 pb-3 border-0" colspan="99">
                                                    <div class="br-table-detail-empty">
                                                        <div class="br-table-detail-empty__top">
                                                            <div class="br-table-detail-empty__body">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 240 200"
                                                                    class="br-table-detail-empty__img"
                                                                    role="img"
                                                                    aria-labelledby="emptyNoMembershipTitle"
                                                                    focusable="false">
                                                                    <title id="emptyNoMembershipTitle">Sin membresías activas</title>
                                                                    <circle cx="108" cy="76" r="28" fill="none" stroke="#556283" stroke-width="2.5" stroke-linecap="round"/>
                                                                    <path
                                                                        d="M48 172c14-46 52-70 60-70s46 24 60 70"
                                                                        fill="none"
                                                                        stroke="#4d5a76"
                                                                        stroke-width="2.25"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"/>
                                                                    <circle cx="174" cy="58" r="24" fill="#ffffff" stroke="#ef4444" stroke-width="2.25"/>
                                                                    <path
                                                                        d="M163 47l22 22M185 47l-22 22"
                                                                        fill="none"
                                                                        stroke="#ef4444"
                                                                        stroke-width="2.75"
                                                                        stroke-linecap="round"/>
                                                                </svg>
                                                                <p class="br-table-detail-empty__text mb-0" v-text="MODULE.texts.emptyActiveMemberships"></p>
                                                            </div>
                                                        </div>
                                                    </div>
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
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal" v-text="MODULE.texts.actions.close"></button>
                </div>
            </div>
        </div>
    </div>

    <PrintSale :modalId="forms[entity].createUpdate.extras.modals.finished.id" :data="forms[entity].createUpdate.extras.modals.finished.data">
        <template v-slot:messageAppend>
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 text-center mb-2">
                <template v-if="forms[entity].createUpdate.extras.modals.finished.data?.extras?.bool">
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle text-success fs-4"></i>
                        <span class="fw-semibold fs-5 ms-2" v-text="forms[entity].createUpdate.extras.modals.finished.data?.extras?.msg"></span>
                    </div>
                </template>
                <template v-else>
                    <div class="alert alert-danger">
                        <i class="fa fa-check-circle text-danger fs-4"></i>
                        <span class="fw-semibold fs-5 ms-2" v-text="forms[entity].createUpdate.extras.modals.finished.data?.extras?.msg"></span>
                    </div>
                </template>
            </div>
        </template>
        <template v-slot:extraGroupAppend>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mx-2">
                <div class="text-center">
                    <button
                        type="button"
                        class="br-btn br-btn-sm br-btn-success waves-effect p-3 rounded mb-1 d-inline-flex align-items-center justify-content-center"
                        data-bs-dismiss="modal">
                        <i class="fa-solid fa-cash-register fs-3" aria-hidden="true"></i>
                    </button>
                    <span class="d-block fw-semibold" v-text="MODULE.texts.actions.newSale"></span>
                </div>
            </div>
            <div class="row g-2 justify-content-center my-4 px-1 px-md-5">
                <InputText
                    hasDiv
                    :placeholder="MODULE.texts.form.whatsappPlaceholder"
                    v-model="forms[entity].createUpdate.extras.modals.finished.data.whatsapp">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-success waves-effect" type="button" @click="sendWhatsapp({data: forms[entity].createUpdate.extras.modals.finished.data})" :disabled="!isDefined({value: forms[entity].createUpdate.extras.modals.finished.data.whatsapp})">
                            <i class="fa-brands fa-whatsapp fs-5" aria-hidden="true"></i>
                            <span class="d-none d-sm-inline ms-sm-2" v-text="MODULE.texts.actions.send"></span>
                        </button>
                    </template>
                </InputText>
                <InputText
                    v-if="false"
                    hasDiv
                    title="Correo electrónico"
                    v-model="forms[entity].createUpdate.extras.modals.finished.data.email">
                    <template v-slot:inputGroupAppend>
                        <button class="btn btn-info-1 waves-effect" type="button" @click="sendEmail({data: forms[entity].createUpdate.extras.modals.finished.data})" :disabled="!isDefined({value: forms[entity].createUpdate.extras.modals.finished.data.email})">
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
import * as Alerts         from "@System/Helpers/Alerts.js";
import * as Constants      from "@System/Helpers/Constants.js";
import { initCrudModule }  from "@System/Helpers/ModuleFactory.js";
import * as Requests       from "@System/Helpers/Requests.js";
import * as Utils          from "@System/Helpers/Utils.js";

const IS_POS_MODE = window.location.pathname.split("?")[0].endsWith("/sales/pos");

const MODULE_CONFIG = {
    entity: "sales",
    menuId: IS_POS_MODE ? "menu-sales-pos" : "menu-sales-create",
    pageTitle: IS_POS_MODE ? "Venta POS" : "Nueva venta",
    breadcrumbParent: IS_POS_MODE ? "Operación" : "Ventas",
    parentMenuId: IS_POS_MODE ? "menu-parent-operations" : "menu-parent-sales"
};

const TEXTS = {
    observations: {
        emptyHint: "Sin observaciones registradas.",
        discreteEmpty: "Sin observaciones",
        modalPlaceholder: "Escriba aquí la observación de la venta…",
        viewMore: "Ver más",
        viewLess: "Ver menos"
    },
    form: {
        branch: "Sucursal",
        serie: "Tipo de comprobante",
        warehouse: "Almacén",
        issueDate: "Fecha de emisión",
        holder: "Cliente",
        observation: "Observaciones",
        commercialCatalog: "Catálogo comercial",
        quantity: "Cantidad",
        quantityPeriods: "Cantidad de períodos",
        price: "Precio",
        total: "Total",
        footerTotalLabel: "Importe total:",
        membershipDetail: "Detalle de la membresía",
        detailRowMembershipLabel: "Membresía",
        startDate: "Fecha de inicio",
        endDate: "Fecha de finalización",
        whatsappPlaceholder: "Número con código de país (ej.: 51987654321)",
        emailPlaceholder: "Correo electrónico (ej.: cliente@empresa.com)"
    },
    actions: {
        addDetail: "Agregar detalle",
        generateSale: "Generar venta",
        viewMemberships: "Ver membresías",
        close: "Cerrar",
        add: "Agregar",
        save: "Guardar",
        delete: "Eliminar",
        duplicate: "Duplicar",
        refresh: "Actualizar",
        newSale: "Nueva venta",
        send: "Enviar",
        openObservations: "Diligenciar observaciones",
        observationAdd: "Agregar observación",
        observationEdit: "Modificar observación",
        saveObservations: "Guardar"
    },
    modal: {
        add: "AGREGAR DETALLE",
        edit: "EDITAR DETALLE",
        activeMemberships: "Membresías activas",
        observations: "Observaciones",
        subscriptionOrigin: "Origen",
        subscriptionCreatedAt: "Fecha de creación"
    },
    emptySaleDetailPrefix: "No hay productos en el detalle. Agréguelos con la acción ",
    emptySaleDetailSuffix: ".",
    emptyActiveMemberships: "No hay membresías activas registradas para este cliente."
};

const MODULE = {
    config: MODULE_CONFIG,
    texts: TEXTS
};

export default {
    name: "SalesMain",
    data() {

        const crudModule = initCrudModule({
            entity: MODULE.config.entity,
            menuId: MODULE.config.menuId,
            pageTitle: MODULE.config.pageTitle
        });

        crudModule.forms[MODULE.config.entity].createUpdate = {
            extras: {
                modals: {
                    details: {
                        id: Utils.uuid(),
                        titles: {
                            add: TEXTS.modal.add,
                            store: TEXTS.modal.add,
                            update: TEXTS.modal.edit
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
                            mode: "add"
                        },
                        errors: {}
                    },
                    observations: {
                        id: Utils.uuid(),
                        draft: ""
                    },
                    subscriptions: {
                        id: Utils.uuid(),
                        titles: {
                            default: TEXTS.modal.activeMemberships
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
                warehouse: null,
                issue_date: "",
                holder: null,
                currency: null,
                observation: "",
                selected_taxes: [],
                selected_tax_quantities: {},
                payments: [],
                status: "",
                details: []
            },
            errors: {
                details: []
            }
        };

        return {
            ...crudModule,
            MODULE: MODULE,
            observationPreviewExpanded: false
        };

    },
    mounted: async function() {

        Utils.navbarItem(MODULE.config.parentMenuId, {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams({});
        const initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            Alerts.swals({show: false});

        }

    },
    methods: {
        taxLabel(tax = {}) {

            const name = tax?.name || "IGV";
            const rate = Number(tax?.rate || 0);
            const calculationType = tax?.calculation_type || "percentage";
            const operationType = tax?.operation_type || "addition";
            const sign = operationType === "subtraction" ? "-" : "+";

            if(calculationType === "fixed") {

                return `${name} ${sign} ${this.separatorNumber(rate)}`;

            }

            return `${name} ${sign}${this.separatorNumber(rate)}%`;

        },
        calculateTaxAmount(tax = {}, baseAmount = 0) {

            const base = Number(baseAmount || 0);
            const rate = Number(tax?.rate || 0);
            const calculationType = tax?.calculation_type || "percentage";
            const operationType = tax?.operation_type || "addition";
            const quantity = this.isFixedTax(tax) ? this.selectedTaxQuantity(tax.id) : 1;
            const amount = calculationType === "fixed"
                ? rate * quantity
                : base * (rate / 100);

            return this.fixedNumber(operationType === "subtraction" ? amount * -1 : amount);

        },
        taxIsRequired(tax = {}) {

            return [true, 1, "1", "true"].includes(tax?.is_required);

        },
        isFixedTax(tax = {}) {

            return (tax?.calculation_type || "percentage") === "fixed";

        },
        selectedTaxQuantity(taxId) {

            const quantities = this.forms[this.entity].createUpdate.data.selected_tax_quantities || {};

            return Math.max(1, parseInt(Number(quantities[taxId] || 1), 10));

        },
        normalizeSelectedTaxQuantity(taxId) {

            const quantities = this.forms[this.entity].createUpdate.data.selected_tax_quantities || {};
            quantities[taxId] = Math.max(1, parseInt(Number(quantities[taxId] || 1), 10));
            this.forms[this.entity].createUpdate.data.selected_tax_quantities = quantities;

        },
        syncSelectedTaxQuantity(tax = {}) {

            if(!this.isFixedTax(tax)) return;

            const quantities = this.forms[this.entity].createUpdate.data.selected_tax_quantities || {};

            if(this.selectedTaxIds().includes(tax.id)) {

                quantities[tax.id] = 1;

            }else {

                quantities[tax.id] = 0;

            }

            this.forms[this.entity].createUpdate.data.selected_tax_quantities = quantities;

        },
        selectedTaxIds() {

            return this.forms[this.entity].createUpdate.data.selected_taxes || [];

        },
        calculateSaleTaxLine(tax = {}) {

            const rate = Number(tax?.rate || 0);
            const calculationType = tax?.calculation_type || "percentage";
            const operationType = tax?.operation_type || "addition";
            let base = 0;
            let amount = 0;
            let totalImpact = 0;

            if(calculationType === "fixed") {

                amount = this.calculateTaxAmount(tax, 0);
                totalImpact = amount;

            }else {

                (this.forms[this.entity].createUpdate.data.details || []).forEach(detail => {
                    const lineTotal = Number(this.calculateTotal({item: detail}) || 0);
                    if(lineTotal <= 0) return;

                    const priceIncludesTax = Boolean(detail?.item?.data?.price_includes_tax ?? detail?.price_includes_tax ?? true);
                    const taxIsIncluded = priceIncludesTax && operationType === "addition" && rate > 0;

                    if(taxIsIncluded) {

                        const lineBase = Number(this.fixedNumber(lineTotal / (1 + (rate / 100))));
                        const lineAmount = Number(this.fixedNumber(lineTotal - lineBase));
                        base += lineBase;
                        amount += lineAmount;
                        return;

                    }

                    const lineAmount = Number(this.calculateTaxAmount(tax, lineTotal));
                    base += lineTotal;
                    amount += lineAmount;
                    totalImpact += lineAmount;

                });

            }

            return {
                id: tax.id,
                name: tax.name || "IGV",
                isRequired: this.taxIsRequired(tax),
                quantity: this.isFixedTax(tax) ? this.selectedTaxQuantity(tax.id) : 1,
                amount: this.fixedNumber(amount),
                totalImpact: this.fixedNumber(totalImpact),
                baseAmount: this.fixedNumber(base)
            };

        },
        // Init
        async initParams({}) {

            const initParams = await Requests.get({route: this.config.entity.routes.initParams, data: {page: "main"}, showAlert: true});

            this.options.branches             = initParams.data?.config?.branches;
            this.options.warehouses           = initParams.data?.config?.warehouses;
            this.options.currencies           = initParams.data?.config?.currencies;
            this.options.holders              = {subscriptions: {}, ...initParams.data?.config?.customers};
            this.options.customers            = initParams.data?.config?.customers;
            this.options.statuses             = initParams.data?.config?.statuses;
            this.options.items                = initParams.data?.config?.items;
            this.options.salesHeader = initParams.data?.config?.salesHeader;
            this.options.taxes = initParams.data?.config?.taxes;
            this.options.paymentMethods = initParams.data?.config?.paymentMethods;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.forms[this.entity].createUpdate.data.branch     = (this.branches).length > 0 ? this.branches[0] : null;
                this.forms[this.entity].createUpdate.data.warehouse  = (this.warehouses).length > 0 ? this.warehouses[0] : null;
                this.forms[this.entity].createUpdate.data.issue_date = Utils.getCurrentDate();
                this.forms[this.entity].createUpdate.data.holder     = (this.holders).length > 0 ? this.holders[0] : null;
                this.forms[this.entity].createUpdate.data.currency   = (this.currencies).length > 0 ? this.currencies[0] : null;
                this.forms[this.entity].createUpdate.data.payments   = [this.newSalePayment({amount: this.total})];

                resolve(true);

            });

        },
        openObservationsModal() {

            const obs = this.forms[this.entity].createUpdate.data.observation;

            this.forms[this.entity].createUpdate.extras.modals.observations.draft = obs == null ? "" : String(obs);

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.observations.id});

        },
        toggleObservationPreviewExpand() {

            this.observationPreviewExpanded = !this.observationPreviewExpanded;

        },
        saveObservationsModal() {

            const draft = this.forms[this.entity].createUpdate.extras.modals.observations.draft;

            this.forms[this.entity].createUpdate.data.observation = draft == null ? "" : String(draft);

            Alerts.modals({type: "hide", id: this.forms[this.entity].createUpdate.extras.modals.observations.id});

        },
        newSalePayment({amount = ""} = {}) {

            return {
                key: Utils.uuid(),
                method: this.salePaymentMethods.find(method => method.data?.is_default) || this.salePaymentMethods[0] || null,
                amount,
                reference: "",
                note: ""
            };

        },
        addSalePayment() {

            const pending = this.salePaymentDifference > 0 ? this.salePaymentDifference : "";

            this.forms[this.entity].createUpdate.data.payments.push(this.newSalePayment({amount: pending}));

        },
        removeSalePayment(index) {

            if(this.forms[this.entity].createUpdate.data.payments.length <= 1) return;

            this.forms[this.entity].createUpdate.data.payments.splice(index, 1);

        },
        // Actions modal detail
        modalAddDetail({}) {

            let form = this.forms[this.entity].createUpdate.extras.modals.details;

            form.data.mode = "add";

            Alerts.modals({type: "show", id: form.id});

        },
        addDetail() {

            const functionName = "addDetail";

            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms[this.entity].createUpdate.extras.modals.details.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                delete form.item.data;

                if(["add"].includes(form.mode)) {

                    (this.forms[this.entity].createUpdate.data.details).push({...form, id: Utils.uuid()});

                    // Alerts.generateAlert({type: "success", msgContent: `Se ha agregado <b><small>(${form?.quantity})</small> ${form?.name}</b> al detalle de la venta.`});
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

            if(Number(operation) > 0) {

                record.quantity = operation;

                this.calculateDuration({record});

            }else {

                Alerts.generateAlert({type: "error", msgContent: this.config.forms.errors.labels.min_number_0});

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

                        (el.forms[el.entity].createUpdate.data.details).splice(keyRecord, 1);

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

                        (el.forms[el.entity].createUpdate.data.details).push(form);

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

            let form = this.forms[this.entity].createUpdate.extras.modals.subscriptions;

            this.refreshSubscriptions();

            Alerts.modals({type: "show", id: form.id});

        },
        async refreshSubscriptions() {

            let form = this.forms[this.entity].createUpdate.extras.modals.subscriptions;

            form.data.loading = true;

            Alerts.tooltips({show: false, time: 0});

            const getSubscriptions = await Utils.getSubscriptions({customer: this.forms[this.entity].createUpdate.data.holder?.data});

            this.options.holders.subscriptions[this.forms[this.entity].createUpdate.data.holder?.data?.id] = Requests.valid({result: getSubscriptions}) ? getSubscriptions?.data?.data?.subscriptions : false;

            form.data.loading = false;

            Alerts.tooltips({show: true});

        },
        // Entity forms
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms[this.entity].createUpdate.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                form.branch_id   = form?.branch?.code;
                form.serie_id    = form?.serie?.code;
                form.warehouse_id = form?.warehouse?.code;
                form.holder_id   = form?.holder?.code;
                form.currency_id = form?.currency?.code;
                form.payments = this.salePaymentPayload;
                form.taxes = this.saleTaxBreakdown.map(tax => ({
                    tax_id: tax.id,
                    quantity: tax.quantity,
                    amount: tax.amount,
                    base_amount: tax.baseAmount,
                    is_required: tax.isRequired
                }));

                delete form.branch;
                delete form.serie;
                delete form.warehouse;
                delete form.holder;
                delete form.currency;
                delete form.selected_taxes;
                delete form.selected_tax_quantities;

                form.details.forEach(detail => {

                    detail.price_includes_tax = Boolean(detail?.item?.data?.price_includes_tax ?? detail?.price_includes_tax ?? true);
                    detail.item_id = detail?.item?.code;
                    detail.currency_id = detail?.currency?.id;

                    delete detail.item;
                    delete detail.currency;

                });

                const createUpdate = await (this.isDefined({value: form.id}) ? Requests.patch({route: this.config.entity.routes.update, data: form, id: form.id}) :
                                                                               Requests.post({route: this.config.entity.routes.store, data: form}));

                if(Requests.valid({result: createUpdate})) {

                    const {sale, ...extras} = createUpdate.data;

                    let holder = this.forms[this.entity].createUpdate.data.holder;

                    const whatsapp = this.isDefined({value: holder.data?.phone_number}) ? holder.data?.phone_number : ""; // this.forms[this.entity].createUpdate.extras.modals.finished.data.whatsapp;
                    const email    = this.isDefined({value: holder.data?.email}) ? holder.data?.email : ""; // this.forms[this.entity].createUpdate.extras.modals.finished.data.email;

                    this.forms[this.entity].createUpdate.extras.modals.finished.data = {...sale, extras, whatsapp, email};

                    Alerts.swals({show: false});
                    Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.finished.id, timeout: 1});

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
        addCustomerPostAction({response = null}) {

            if(Requests.valid({result: response}) && this.isDefined({value: response?.data?.customer})) {

                (this.options.holders.records).push(response?.data?.customer);

            }

        },
        clearForm({functionName}) {

            switch(functionName) {
                case "addDetail":
                    this.forms[this.entity].createUpdate.extras.modals.details.data.id   = null;
                    this.forms[this.entity].createUpdate.extras.modals.details.data.item = null;
                    break;

                case "createUpdateEntity":
                    this.forms[this.entity].createUpdate.data.id          = null;
                    // this.forms[this.entity].createUpdate.data.issue_date  = Utils.getCurrentDate();
                    // this.forms[this.entity].createUpdate.data.holder      = null;
                    this.forms[this.entity].createUpdate.data.observation = "";
                    this.forms[this.entity].createUpdate.data.warehouse   = (this.warehouses).length > 0 ? this.warehouses[0] : null;
                    this.forms[this.entity].createUpdate.extras.modals.observations.draft = "";
                    this.forms[this.entity].createUpdate.data.selected_taxes = [];
                    this.forms[this.entity].createUpdate.data.selected_tax_quantities = {};
                    this.forms[this.entity].createUpdate.data.payments   = [this.newSalePayment({amount: this.total})];
                    this.forms[this.entity].createUpdate.data.status      = "";
                    this.forms[this.entity].createUpdate.data.details     = [];
                    break;
            }

        },
        formErrors({functionName, type = "clear", errors = []}) {

            if(["addDetail"].includes(functionName)) {

                this.forms[this.entity].createUpdate.extras.modals.details.errors = ["set"].includes(type) ? errors : [];

            }else if(["createUpdateEntity"].includes(functionName)) {

                this.forms[this.entity].createUpdate.errors = ["set"].includes(type) ? errors : [];

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

                            const subscriptions = Utils.cloneJson(this.options?.holders?.subscriptions[this.forms[this.entity].createUpdate.data.holder?.data?.id]);

                            let findOverlaps = Utils.findOverlaps({start_date: form?.extras?.start_date, end_date: form?.extras?.end_date}, subscriptions);

                            if(findOverlaps.hasOverlap) {

                                let messages = findOverlaps.positions.map(e =>
                                    `<div class="mt-3">
                                        <span class="d-block fw-bold">Membresía activa #${parseInt(e?.keyArray) + 1}</span>
                                        <div class="d-block mt-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="fw-semibold ms-1">F. inicio:</span>
                                            <span class="br-status-label br-status-label--primary fw-bold">${this.legibleFormatDate({dateString: e?.start_date})}</span>
                                        </div>
                                        <div class="d-block mt-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span class="fw-semibold ms-1">F. final.:</span>
                                            <span class="br-status-label br-status-label--success fw-bold">${this.legibleFormatDate({dateString: e?.end_date})}</span>
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
                result.warehouse   = [];
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

                if(!this.isDefined({value: form?.warehouse})) {

                    result.warehouse.push(`${isDescriptive ? "Almacén:" : ""} ${this.config.forms.errors.labels.required}`);
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
        isDurationTypeCalendar(durationType) {

            return ["day", "month", "year"].includes(durationType);

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

                    record.extras.formatted_total_duration = `${this.separatorNumber(durationTotal)} `+(durationTotal > 1 ? durationTypeLegible?.plural : durationTypeLegible?.label);

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
        tooltips({show = true, time = 10}) {

            Alerts.tooltips({show, time});

        },
        sendWhatsapp({data = null, action = "reportSale"}) {

            const phoneNumber = this.forms[this.entity].createUpdate.extras.modals.finished.data.whatsapp;
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
        entity() {

            return this.MODULE.config.entity;

        },
        breadcrumbTitles() {

            return [{title: MODULE.config.breadcrumbParent}, this.config.entity.page];

        },
        customerOptions() {

            const { records, ...rest } = this.options.customers ?? {};

            return rest;

        },
        branches: function() {

            return this.options?.branches?.records.map(e => ({code: e.id, label: e.name, data: e}));

        },
        series: function() {

            const branch = (this.options?.branches?.records ?? []).filter(e => e?.id == this.forms[this.entity].createUpdate.data.branch?.code);

            if(branch.length === 1) {

                const series = branch[0].series;

                return series.map(e => ({code: e.id, label: `${e.legible_serie} - ${e?.document_type?.name}`, data: e}));

            }

            return [];

        },
        warehouses: function() {

            const branchId = this.forms[this.entity].createUpdate.data.branch?.code;

            return (this.options?.warehouses?.records ?? [])
                .filter(e => !branchId || e?.branch_id == branchId)
                .map(e => ({
                    code: e.id,
                    label: `${e?.branch?.name ?? "Sucursal"} - ${e.name}`,
                    data: e
                }));

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
        saleTaxes() {

            return (this.options?.taxes?.records || []).map(tax => ({
                code: tax.id,
                label: this.taxLabel(tax),
                data: tax
            }));

        },
        requiredSaleTaxes() {

            return this.saleTaxes.filter(tax => this.taxIsRequired(tax.data));

        },
        optionalSaleTaxes() {

            return this.saleTaxes.filter(tax => !this.taxIsRequired(tax.data));

        },
        appliedSaleTaxes() {

            const selected = this.selectedTaxIds();

            return this.saleTaxes.filter(tax => this.taxIsRequired(tax.data) || selected.includes(tax.code));

        },
        salePaymentMethods() {

            return (this.options?.paymentMethods?.records || []).map(method => ({
                code: method.id,
                label: method.name,
                data: method
            }));

        },
        saleGrossSubtotal: function() {

            let total = 0;

            for(let detail of this.forms[this.entity].createUpdate.data.details) {

                total += Number(this.calculateTotal({item: detail}));

            }

            return this.fixedNumber(total);

        },
        saleSubtotal: function() {

            return this.fixedNumber(Number(this.saleGrossSubtotal || 0) - Number(this.saleIncludedTaxTotal || 0));

        },
        saleTaxTotal() {

            return this.fixedNumber((this.saleTaxBreakdown || []).reduce((total, tax) => {
                return total + Number(tax.amount || 0);
            }, 0));

        },
        saleTaxImpactTotal() {

            return this.fixedNumber((this.saleTaxBreakdown || []).reduce((total, tax) => {
                return total + Number(tax.totalImpact || 0);
            }, 0));

        },
        saleIncludedTaxTotal() {

            return this.fixedNumber(Number(this.saleTaxTotal || 0) - Number(this.saleTaxImpactTotal || 0));

        },
        saleTaxBreakdown() {

            return (this.appliedSaleTaxes || []).map(tax => {
                const data = tax.data || {};

                return this.calculateSaleTaxLine(data);
            });

        },
        saleAdditionalTaxBase() {

            return this.fixedNumber((this.forms[this.entity].createUpdate.data.details || []).reduce((total, detail) => {

                const priceIncludesTax = Boolean(detail?.item?.data?.price_includes_tax ?? detail?.price_includes_tax ?? true);

                if(priceIncludesTax) return total;

                return total + Number(this.calculateTotal({item: detail}));

            }, 0));

        },
        total: function() {

            return this.fixedNumber(Number(this.saleGrossSubtotal || 0) + Number(this.saleTaxImpactTotal || 0));

        },
        salePaymentPayload() {

            const selected = this.forms[this.entity].createUpdate.data.payments || [];

            if(selected.length === 0) return [];

            return selected
                .filter(payment => payment.method?.code)
                .map(payment => ({
                    payment_method_id: payment.method.code,
                    amount: Number(payment.amount || 0),
                    reference: payment.reference || null,
                    note: payment.note || null
                }));

        },
        salePaidTotal() {

            return this.fixedNumber((this.forms[this.entity].createUpdate.data.payments || []).reduce((total, payment) => {
                return total + Number(payment.amount || 0);
            }, 0));

        },
        salePaymentDifference() {

            return this.fixedNumber(Number(this.total || 0) - Number(this.salePaidTotal || 0));

        },
        saleDetailEmptyImageUrl() {

            return "/System/assets/img/utils/without_data/empty_sale_detail.svg";

        },
        observationFullText() {

            const raw = this.forms[this.entity].createUpdate.data.observation;

            if(!this.isDefined({value: raw})) return "";

            return String(raw).trim();

        },
        observationHasContent() {

            return this.observationFullText.length > 0;

        },
        /** Preview length before "Ver más" (sidebar). */
        observationPreviewCharLimit() {

            return 400;

        },
        observationIsTruncatable() {

            return this.observationFullText.length > this.observationPreviewCharLimit;

        },
        observationDisplayPreview() {

            const full = this.observationFullText;

            if(!full) return "";

            if(this.observationPreviewExpanded || !this.observationIsTruncatable) return full;

            const max = this.observationPreviewCharLimit;

            return `${full.slice(0, max)}…`;

        },
        observationPreviewTooltip() {

            if(!this.observationHasContent || this.observationPreviewExpanded || !this.observationIsTruncatable) return "";

            return this.observationFullText;

        },
        observationPreviewToggleLabel() {

            return this.observationPreviewExpanded ? this.MODULE.texts.observations.viewLess : this.MODULE.texts.observations.viewMore;

        },
        observationErrorsDisplay() {

            const err = this.forms[this.entity].createUpdate.errors?.observation;

            if(!err) return "";

            return Array.isArray(err) ? err.join("<br/>") : String(err);

        },
        observationsCtaButtonLabel() {

            const raw = this.forms[this.entity].createUpdate.data.observation;
            const has = raw != null && String(raw).trim() !== "";

            return has ? this.MODULE.texts.actions.observationEdit : this.MODULE.texts.actions.observationAdd;

        },
        observationsFieldAriaLabel() {

            return `${this.MODULE.texts.form.observation}. ${this.observationsCtaButtonLabel}`;

        },
        totalModalDetail: function() {

            return this.calculateTotal({item: this.forms[this.entity].createUpdate.extras.modals.details.data});

        },
        modalDetailsTitle() {

            const mode   = this.forms[this.entity].createUpdate.extras.modals.details.data?.mode;
            const titles = this.forms[this.entity].createUpdate.extras.modals.details.titles;

            return titles?.[mode] ?? "";

        }
    },
    watch: {
        "forms.sales.createUpdate.data.observation"() {

            this.observationPreviewExpanded = false;

        },
        total(value) {

            const payments = this.forms[this.entity].createUpdate.data.payments || [];

            if(payments.length === 1) {

                payments[0].amount = Number(value || 0);

            }

        },
        "forms.sales.createUpdate.data.branch"() {

            this.forms[this.entity].createUpdate.data.serie = (this.series).length > 0 ? this.series[0] : null;
            this.forms[this.entity].createUpdate.data.warehouse = (this.warehouses).length > 0 ? this.warehouses[0] : null;

        },
        "forms.sales.createUpdate.data.holder": async function(newValue) {

            const getSubscriptions = await Utils.getSubscriptions({customer: newValue?.data});

            this.options.holders.subscriptions[newValue?.data?.id] = Requests.valid({result: getSubscriptions}) ? getSubscriptions?.data?.subscriptions : false;

        },
        "forms.sales.createUpdate.extras.modals.details.data.item": function(newValue) {

            const data = newValue?.data;

            const modalData = this.forms[this.entity].createUpdate.extras.modals.details.data;

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
                    force: false,
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
