<template>
    <Breadcrumb :list="breadcrumbTitles"/>

    <!-- Content -->
    <div class="br-sale-create-grid">
        <div class="br-sale-create-main">
            <div class="card">
                <div class="card-body">
                    <div class="row g-2 mb-2">
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
                                    placeholder="Seleccione">
                                    <template #selected-option="option">
                                        <span class="br-document-serie-option">
                                            <strong v-text="option.label"></strong>
                                            <small v-text="option.description"></small>
                                        </span>
                                    </template>
                                    <template #option="option">
                                        <span class="br-document-serie-option">
                                            <strong v-text="option.label"></strong>
                                            <small v-text="option.description"></small>
                                        </span>
                                    </template>
                                </v-select>
                            </template>
                        </InputSlot>
                        <div v-if="saleConfigurationIssue" class="col-12">
                            <div class="alert alert-warning d-flex align-items-start gap-2 px-3 py-2 mb-0" role="alert">
                                <i class="fa-solid fa-triangle-exclamation mt-1" aria-hidden="true"></i>
                                <div>
                                    <strong class="d-block" v-text="saleConfigurationIssue.title"></strong>
                                    <span v-text="saleConfigurationIssue.message"></span>
                                </div>
                            </div>
                        </div>
                        <InputDate
                            v-model="forms[entity].createUpdate.data.issue_date"
                            hasDiv
                            :title="MODULE.texts.form.issueDate"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            disabled
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.issue_date"
                            xl="4"
                            lg="4"/>
                        <InputSlot
                            hasDiv
                            :title="MODULE.texts.form.holder"
                            :titleClass="[config.forms.classes.title]"
                            isRequired
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.errors?.holder_id"
                            xl="8"
                            lg="8">
                            <template v-slot:default>
                                <AddCustomer
                                    v-if="!hasQuotationApplied"
                                    :options="customerOptions"
                                    @postAction="addCustomerPostAction"/>
                            </template>
                            <template v-slot:defaultAppend>
                                <a
                                    href="javascript:void(0)"
                                    class="br-link br-quick-create-trigger br-quick-create-trigger--link br-sale-customer-history-trigger ms-2"
                                    :aria-disabled="!forms[entity].createUpdate.data.holder"
                                    :title="MODULE.texts.actions.viewCustomerHistory"
                                    @click.prevent="forms[entity].createUpdate.data.holder && viewCustomerHistory()">
                                    <span v-text="MODULE.texts.actions.viewCustomerHistory"></span>
                                </a>
                            </template>
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.data.holder"
                                    :options="holders"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :disabled="hasQuotationApplied"
                                    :searchable="true"
                                    placeholder="Seleccione">
                                    <template #selected-option="option">
                                        <span class="br-document-holder-option">
                                            <strong v-text="holderDocumentTypeLabel(option)"></strong>
                                            <span v-text="holderDocumentDescription(option)"></span>
                                        </span>
                                    </template>
                                    <template #option="option">
                                        <span class="br-document-holder-option">
                                            <strong v-text="holderDocumentTypeLabel(option)"></strong>
                                            <span v-text="holderDocumentDescription(option)"></span>
                                        </span>
                                    </template>
                                </v-select>
                            </template>
                        </InputSlot>
                    </div>
                    <div class="br-sale-detail-toolbar">
                        <span
                            class="br-sale-detail-toolbar__counter"
                            v-text="`Detalle de la venta · ${forms[entity].createUpdate.data.details.length} ${forms[entity].createUpdate.data.details.length === 1 ? 'ítem' : 'ítems'}`">
                        </span>
                        <div class="br-sale-detail-toolbar__actions">
                            <button type="button" class="br-btn br-btn-sm br-btn-primary waves-effect" @click="modalAddDetail({})">
                                <span v-text="MODULE.texts.actions.addDetail"></span>
                            </button>
                            <button
                                type="button"
                                class="br-btn br-btn-sm br-btn-cancel br-sale-detail-toolbar__clear waves-effect"
                                :disabled="forms[entity].createUpdate.data.details.length <= 0"
                                @click="clearDetails">
                                <span>Limpiar ítems</span>
                            </button>
                        </div>
                    </div>
                    <div class="br-sale-detail-table-shell">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width: 10%;">#</th>
                                        <th class="min-w-150px" style="width: 20%;">ÍTEM</th>
                                        <th class="min-w-150px" style="width: 20%;">CANTIDAD</th>
                                        <th class="min-w-150px" style="width: 20%;">PRECIO UNITARIO</th>
                                        <th class="min-w-150px text-end pe-3" style="width: 20%;">TOTAL</th>
                                        <th style="width: 10%;" aria-label="Acciones"></th>
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
                                                    <div class="br-sale-detail-qty-actions">
                                                        <button
                                                            class="br-sale-detail-qty-btn br-sale-detail-qty-btn--minus waves-effect"
                                                            type="button"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Restar unidad"
                                                            aria-label="Restar unidad"
                                                            :disabled="Number(record.quantity ?? 0) <= 1"
                                                            @click="changeQuantityDetail({record, keyRecord, type: 'subtract'})">
                                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                                        </button>
                                                        <button
                                                            class="br-sale-detail-qty-btn br-sale-detail-qty-btn--plus waves-effect"
                                                            type="button"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Agregar unidad"
                                                            aria-label="Agregar unidad"
                                                            @click="changeQuantityDetail({record, keyRecord, type: 'add'})">
                                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <InputNumber v-model="record.price">
                                                        <template v-slot:inputGroupPrepend v-if="isDefined({value: record?.currency})">
                                                            <span class="input-group-text br-currency-prefix" v-text="record?.currency?.sign"></span>
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
                                                    <div class="br-sale-detail-actions">
                                                        <button
                                                            class="br-sale-detail-action br-sale-detail-action--warning waves-effect"
                                                            type="button"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Rectificar ítem"
                                                            aria-label="Rectificar ítem"
                                                            @click="modalEditDetail({record, keyRecord})">
                                                            <i class="fa fa-pen" aria-hidden="true"></i>
                                                        </button>
                                                        <button
                                                            class="br-sale-detail-action br-sale-detail-action--danger waves-effect"
                                                            type="button"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Eliminar ítem"
                                                            aria-label="Eliminar ítem"
                                                            @click="deleteDetail({record, keyRecord})">
                                                            <i class="fa fa-times" aria-hidden="true"></i>
                                                        </button>
                                                        <button
                                                            v-if="!isSubscription(record?.type)"
                                                            class="br-sale-detail-action br-sale-detail-action--primary waves-effect"
                                                            type="button"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Duplicar ítem"
                                                            aria-label="Duplicar ítem"
                                                            @click="duplicateDetail({record, keyRecord})">
                                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                                        </button>
                                                        <template v-if="isSubscription(record?.type)">
                                                            <button
                                                                class="br-sale-detail-action br-sale-detail-action--success waves-effect"
                                                                type="button"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Ver detalle"
                                                                aria-label="Ver detalle"
                                                                @click="viewDetail({record, keyRecord})">
                                                                <i :class="record?.extras?.showDetail ? 'fa fa-eye-slash' : 'fa fa-eye'" aria-hidden="true"></i>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                            <template v-if="record?.extras?.showDetail">
                                                <template v-if="isSubscription(record?.type)">
                                                    <tr class="br-sale-detail-membership-row">
                                                        <td colspan="5">
                                                            <div class="br-sale-detail-membership">
                                                                <div class="br-sale-detail-membership__heading">
                                                                    <strong class="colon-at-end" v-text="MODULE.texts.form.detailRowMembershipLabel"></strong>
                                                                    <small>Vigencia aplicada al detalle</small>
                                                                </div>
                                                                <div class="br-sale-detail-membership__fields">
                                                                    <div class="br-sale-detail-membership__date">
                                                                        <span class="colon-at-end">Fecha de inicio</span>
                                                                        <strong v-text="formatSaleDetailDateTime(record.extras.start_date)"></strong>
                                                                    </div>
                                                                    <div class="br-sale-detail-membership__date">
                                                                        <span class="colon-at-end">Fecha de finalización</span>
                                                                        <strong v-text="formatSaleDetailDateTime(record.extras.end_date)"></strong>
                                                                    </div>
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
                                                            <strong class="br-table-detail-empty__title" v-text="MODULE.texts.emptySaleDetailTitle"></strong>
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
        <div class="br-sale-create-side">
            <div class="br-document-settlement br-sale-settlement bg-white">
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Observaciones</label>
                        <button
                            type="button"
                            class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Cambiar observaciones"
                            aria-label="Cambiar observaciones"
                            @click="openObservationsModal">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="br-document-observation-summary">
                        <span
                            v-if="observationHasContent"
                            class="br-document-observation-summary__value"
                            :title="observationPreviewTooltip"
                            v-text="observationDisplayPreview">
                        </span>
                        <p v-else class="br-document-settlement__empty mb-0">
                            Sin observaciones para esta venta.
                        </p>
                    </div>
                    <p
                        v-if="forms[entity].createUpdate.errors?.observation?.length"
                        class="small mb-0 mt-2"
                        :class="config.forms.errors.styles.default"
                        v-html="observationErrorsDisplay"></p>
                </div>
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Cotización</label>
                        <div class="br-document-settlement__actions">
                            <button
                                type="button"
                                class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Cambiar cotización"
                                aria-label="Cambiar cotización"
                                @click="openQuotationModal">
                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                            </button>
                            <button
                                v-if="hasQuotationApplied"
                                type="button"
                                class="br-btn br-btn-xs br-document-payment-config br-document-payment-config--danger waves-effect"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Quitar cotización"
                                aria-label="Quitar cotización"
                                @click="clearQuotation">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="br-document-quotation-summary">
                        <template v-if="hasQuotationApplied">
                            <strong v-text="forms[entity].createUpdate.data.quotation?.data?.reference || 'Cotización aplicada'"></strong>
                            <small v-text="quotationSummaryLabel"></small>
                        </template>
                        <p v-else class="br-document-settlement__empty mb-0">
                            Sin cotización para esta venta.
                        </p>
                    </div>
                </div>
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Vendedor</label>
                        <button
                            type="button"
                            class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Cambiar vendedor"
                            aria-label="Cambiar vendedor"
                            @click="openSellerModal">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="br-document-delivery-summary">
                        <span class="br-document-delivery-summary__value" v-text="sellerLabel"></span>
                        <small v-if="forms[entity].createUpdate.errors?.seller" :class="config.forms.errors.styles.default" v-html="forms[entity].createUpdate.errors.seller"></small>
                    </div>
                </div>
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Tipo de entrega</label>
                        <button
                            type="button"
                            class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Cambiar tipo de entrega"
                            aria-label="Cambiar tipo de entrega"
                            @click="openDeliveryModal">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="br-document-delivery-summary">
                        <span class="br-document-delivery-summary__value" v-text="deliveryModeLabel"></span>
                        <small v-if="forms[entity].createUpdate.errors?.delivery_mode" :class="config.forms.errors.styles.default" v-html="forms[entity].createUpdate.errors.delivery_mode"></small>
                    </div>
                </div>
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Almacén</label>
                        <button
                            v-if="canChangeWarehouse"
                            type="button"
                            class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Cambiar almacén"
                            aria-label="Cambiar almacén"
                            @click="openWarehouseModal">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="br-document-delivery-summary">
                        <span class="br-document-delivery-summary__value" v-text="warehouseLabel"></span>
                        <small v-if="forms[entity].createUpdate.errors?.warehouse" :class="config.forms.errors.styles.default" v-html="forms[entity].createUpdate.errors.warehouse"></small>
                    </div>
                </div>
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Impuestos extras</label>
                        <button
                            v-if="canChangeTaxes"
                            type="button"
                            class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Cambiar impuestos extras"
                            aria-label="Cambiar impuestos extras"
                            @click="openTaxesModal">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="br-document-tax-summary">
                        <template v-if="saleOptionalTaxSummary.length">
                            <div
                                v-for="tax in saleOptionalTaxSummary"
                                :key="tax.key"
                                class="br-document-tax-summary__row">
                                <span>
                                    <strong v-text="tax.name"></strong>
                                    <small v-text="tax.description"></small>
                                </span>
                                <b>
                                    {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                                    {{ separatorNumber(tax.amount) }}
                                </b>
                            </div>
                        </template>
                        <p v-else class="br-document-settlement__empty mb-0">
                            Sin impuestos extras para esta venta.
                        </p>
                    </div>
                </div>
                <div>
                    <div class="br-document-settlement__header">
                        <label class="form-label colon-at-end">Métodos de pago</label>
                        <button
                            v-if="canChangePaymentMethods"
                            type="button"
                            class="br-btn br-btn-xs br-btn-action-import br-document-payment-config waves-effect"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Cambiar métodos de pago"
                            aria-label="Cambiar métodos de pago"
                            @click="openPaymentMethodsModal">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="br-document-payment-summary">
                        <template v-if="salePaymentSummary.length">
                            <div
                                v-for="payment in salePaymentSummary"
                                :key="payment.key"
                                class="br-document-payment-summary__row">
                                <span>
                                    <strong v-text="payment.label"></strong>
                                    <small v-if="payment.reference" v-text="payment.reference"></small>
                                </span>
                                <b>
                                    {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                                    {{ separatorNumber(payment.amount) }}
                                </b>
                            </div>
                        </template>
                        <p v-else class="br-document-settlement__empty mb-0">
                            Sin métodos de pago para esta venta.
                        </p>
                    </div>
                </div>
                <div class="br-document-settlement__summary-section">
                    <h3 class="br-document-summary-card__title">Resumen</h3>
                    <div class="br-document-settlement__summary">
                        <span class="br-document-settlement__summary-label br-document-settlement__summary-label--primary">Subtotal</span>
                        <strong class="br-document-settlement__summary-value br-document-settlement__summary-value--primary">
                            {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                            {{ separatorNumber(saleSubtotal) }}
                        </strong>
                        <template v-if="saleTaxBreakdown.length">
                            <template v-for="tax in saleTaxBreakdown" :key="`sale-summary-tax-${tax.id}`">
                                <span class="br-document-settlement__summary-label br-document-settlement__summary-label--primary">{{ tax.name }}</span>
                                <strong class="br-document-settlement__summary-value br-document-settlement__summary-value--primary">
                                    {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                                    {{ separatorNumber(tax.amount) }}
                                </strong>
                            </template>
                        </template>
                        <template v-else>
                            <span class="br-document-settlement__summary-label br-document-settlement__summary-label--primary">IGV</span>
                            <strong class="br-document-settlement__summary-value br-document-settlement__summary-value--primary">
                                {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                                0.00
                            </strong>
                        </template>
                        <span class="br-document-settlement__summary-label br-document-settlement__summary-label--total">Total</span>
                        <strong class="br-document-settlement__summary-value br-document-settlement__summary-value--total">
                            {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                            {{ separatorNumber(total) }}
                        </strong>
                        <span class="br-document-settlement__summary-label">
                            <span>Pagado</span>
                            <i
                                class="fa-solid fa-circle-info br-document-settlement__summary-help"
                                aria-label="Sumatoria de métodos de pago"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                role="img"
                                tabindex="0"
                                title="Sumatoria de métodos de pago"></i>
                        </span>
                        <strong class="br-document-settlement__summary-value">
                            {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                            {{ separatorNumber(salePaidTotal) }}
                        </strong>
                        <span class="br-document-settlement__summary-label">Diferencia</span>
                        <strong class="br-document-settlement__summary-value">
                            {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                            {{ separatorNumber(salePaymentDifference) }}
                        </strong>
                    </div>
                </div>
                <div class="br-document-settlement__submit-section">
                    <button
                        type="button"
                        class="br-btn br-btn-success br-sale-sidebar-actions__cta waves-effect"
                        :disabled="Boolean(saleSubmitBlocker)"
                        :title="saleSubmitBlocker?.message || MODULE.texts.actions.generateSale"
                        @click="createUpdateEntity()">
                        <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
                        <span v-text="MODULE.texts.actions.generateSale"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.branch.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar sucursal</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <InputSlot
                        hasDiv
                        :title="MODULE.texts.form.branch"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.branch"
                        xl="12"
                        lg="12">
                        <template v-slot:input>
                            <v-select
                                v-model="forms[entity].createUpdate.data.branch"
                                :options="branches"
                                :class="config.forms.classes.select2"
                                :clearable="false"
                                :searchable="false"
                                append-to-body
                                placeholder="Seleccione"/>
                        </template>
                    </InputSlot>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar sucursal</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.warehouse.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar almacén</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <InputSlot
                        hasDiv
                        :title="MODULE.texts.form.warehouse"
                        :titleClass="[config.forms.classes.title]"
                        isRequired
                        hasTextBottom
                        :textBottomInfo="forms[entity].createUpdate.errors?.warehouse"
                        xl="12"
                        lg="12">
                        <template v-slot:input>
                            <v-select
                                v-model="forms[entity].createUpdate.data.warehouse"
                                :options="warehouses"
                                :class="config.forms.classes.select2"
                                :clearable="false"
                                :searchable="false"
                                append-to-body
                                placeholder="Seleccione"/>
                        </template>
                    </InputSlot>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar almacén</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.delivery.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar entrega</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <div class="br-choice-group br-choice-group--stacked" role="radiogroup" :aria-label="MODULE.texts.form.deliveryMode">
                        <label
                            v-for="mode in deliveryModes"
                            :key="mode.code"
                            :class="['br-choice-option', {'is-selected': forms[entity].createUpdate.data.delivery_mode?.code === mode.code}]">
                            <input
                                class="br-choice-option__input"
                                type="radio"
                                name="sale_delivery_mode"
                                :value="mode.code"
                                :checked="forms[entity].createUpdate.data.delivery_mode?.code === mode.code"
                                @change="forms[entity].createUpdate.data.delivery_mode = mode">
                            <span class="br-choice-option__indicator" aria-hidden="true"></span>
                            <span>
                                <strong v-text="mode.label"></strong>
                                <small v-text="mode.description"></small>
                            </span>
                        </label>
                    </div>
                    <small
                        v-if="forms[entity].createUpdate.errors?.delivery_mode"
                        :class="config.forms.errors.styles.default"
                        v-html="forms[entity].createUpdate.errors.delivery_mode"></small>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar entrega</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.quotation.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar cotización</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <InputSlot
                        hasDiv
                        :title="MODULE.texts.form.quotation"
                        :titleClass="[config.forms.classes.title]"
                        xl="12"
                        lg="12">
                        <template v-slot:input>
                            <v-select
                                v-model="forms[entity].createUpdate.data.quotation"
                                :options="quotationOptions"
                                :class="config.forms.classes.select2"
                                :clearable="false"
                                :searchable="true"
                                append-to-body
                                placeholder="Seleccione cotización"
                                @option:selected="applyQuotationDraft"/>
                        </template>
                    </InputSlot>
                    <p class="br-document-settlement__empty mb-0 mt-2">
                        Al aplicar una cotización, el cliente queda bloqueado para conservar la trazabilidad.
                    </p>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar cotización</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.seller.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar vendedor</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <InputSlot
                        hasDiv
                        title="Vendedor"
                        :titleClass="[config.forms.classes.title]"
                        xl="12"
                        lg="12">
                        <template v-slot:input>
                            <v-select
                                v-model="forms[entity].createUpdate.data.seller"
                                :options="users"
                                :class="config.forms.classes.select2"
                                :clearable="false"
                                :searchable="true"
                                append-to-body
                                placeholder="Seleccione vendedor"/>
                        </template>
                    </InputSlot>
                    <p class="br-document-settlement__empty mb-0 mt-2">
                        Las comisiones de la venta quedarán asociadas al vendedor seleccionado.
                    </p>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar vendedor</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.taxes.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar impuestos extras</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <p class="br-document-payment-intro">
                        Activa solo los cargos adicionales que aplican para esta venta.
                    </p>
                    <div class="br-document-taxes-modal" v-if="optionalSaleTaxes.length">
                        <div
                            v-for="tax in optionalSaleTaxes"
                            :key="`optional-sale-tax-modal-${tax.code}`"
                            class="br-document-tax-option">
                            <label class="br-entity-switch br-document-tax-option__switch">
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
                                :minValue="taxQuantityMinimum(tax.data)"
                                :maxValue="taxQuantityMaximum(tax.data)"
                                :hasNegative="false"
                                @change="normalizeSelectedTaxQuantity(tax.code)">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text br-tax-quantity__label">Veces</span>
                                </template>
                            </InputNumber>
                        </div>
                    </div>
                    <p v-else class="br-document-settlement__empty mb-0">
                        No hay impuestos extras configurados para ventas.
                    </p>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar impuestos</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.payments.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold">Cambiar métodos de pago</h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <p class="br-document-payment-intro">
                        Distribuye el importe total entre uno o más métodos configurados para ventas.
                    </p>
                    <div class="br-document-payments br-document-payments--modal">
                        <div
                            v-for="(payment, index) in forms[entity].createUpdate.data.payments"
                            :key="payment.key"
                            class="br-document-payment-row">
                            <v-select
                                v-model="payment.method"
                                :options="salePaymentMethods"
                                :clearable="false"
                                :searchable="true"
                                append-to-body
                                @update:modelValue="syncSalePaymentVariant(payment)">
                                <template #selected-option="{ label, data }">
                                    <span class="br-payment-select-option">
                                        <img
                                            v-if="paymentAssetUrl(data)"
                                            :src="paymentAssetUrl(data)"
                                            alt=""
                                            class="br-payment-select-option__image">
                                        <span>{{ label }}</span>
                                    </span>
                                </template>
                                <template #option="{ label, data }">
                                    <span class="br-payment-select-option">
                                        <img
                                            v-if="paymentAssetUrl(data)"
                                            :src="paymentAssetUrl(data)"
                                            alt=""
                                            class="br-payment-select-option__image">
                                        <span>{{ label }}</span>
                                    </span>
                                </template>
                            </v-select>
                            <v-select
                                v-if="salePaymentVariantOptions(payment).length"
                                v-model="payment.variant"
                                :options="salePaymentVariantOptions(payment)"
                                :clearable="false"
                                :searchable="true"
                                append-to-body
                                placeholder="Variante">
                                <template #selected-option="{ label, data }">
                                    <span class="br-payment-select-option">
                                        <img
                                            v-if="paymentAssetUrl(data)"
                                            :src="paymentAssetUrl(data)"
                                            alt=""
                                            class="br-payment-select-option__image">
                                        <span>{{ label }}</span>
                                    </span>
                                </template>
                                <template #option="{ label, data }">
                                    <span class="br-payment-select-option">
                                        <img
                                            v-if="paymentAssetUrl(data)"
                                            :src="paymentAssetUrl(data)"
                                            alt=""
                                            class="br-payment-select-option__image">
                                        <span>{{ label }}</span>
                                    </span>
                                </template>
                            </v-select>
                            <InputNumber
                                v-model="payment.amount"
                                title=""
                                :titleClass="[]"
                                :inputClass="['form-control', 'br-document-payment-amount']"
                                :minValue="0"
                                :placeholder="separatorNumber(total)">
                                <template v-slot:inputGroupPrepend>
                                    <span class="input-group-text br-currency-prefix" v-text="forms[entity].createUpdate.data.currency?.data?.sign ?? ''"></span>
                                </template>
                            </InputNumber>
                            <input
                                v-if="paymentRequiresReference(payment)"
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
                        <div class="br-document-payment-modal-total">
                            <span>Diferencia</span>
                            <strong :class="Number(salePaymentDifference) === 0 ? 'text-success' : 'text-danger'">
                                {{ forms[entity].createUpdate.data.currency?.data?.sign ?? '' }}
                                {{ separatorNumber(salePaymentDifference) }}
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary" data-bs-dismiss="modal">Cambiar métodos de pago</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.details.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="modalDetailsTitle"></h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
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
                                    :disabled="isModalDetailUpdate"
                                    :searchable="true"
                                    append-to-body
                                    placeholder="Seleccione">
                                    <template #selected-option="{ label }">
                                        <span class="br-sale-catalog-selected-option">
                                            <span class="br-sale-catalog-selected-option__label" v-text="label"></span>
                                        </span>
                                    </template>
                                    <template #option="{ data }">
                                        <div class="br-sale-catalog-option">
                                            <div class="br-sale-catalog-option__head">
                                                <strong v-text="data?.name"></strong>
                                                <span v-if="data?.formatted_type" class="br-sale-catalog-option__type" v-text="data?.formatted_type"></span>
                                            </div>
                                            <div class="br-sale-catalog-option__line">
                                                <div class="br-sale-catalog-option__meta">
                                                    <span class="br-sale-catalog-option__meta-card is-price">
                                                        <i class="fa fa-money-bill" aria-hidden="true"></i>
                                                        <small>Venta</small>
                                                        <strong v-text="`${data?.currency?.sign ?? ''} ${separatorNumber(data?.price)}`"></strong>
                                                    </span>
                                                    <span v-if="isDefined({value: data?.min_price})" class="br-sale-catalog-option__meta-card is-range is-min">
                                                        <i class="fa fa-arrow-down" aria-hidden="true"></i>
                                                        <small>Mín.</small>
                                                        <strong v-text="`${data?.currency?.sign ?? ''} ${separatorNumber(data?.min_price)}`"></strong>
                                                    </span>
                                                    <span v-if="isDefined({value: data?.max_price})" class="br-sale-catalog-option__meta-card is-range is-max">
                                                        <i class="fa fa-arrow-up" aria-hidden="true"></i>
                                                        <small>Máx.</small>
                                                        <strong v-text="`${data?.currency?.sign ?? ''} ${separatorNumber(data?.max_price)}`"></strong>
                                                    </span>
                                                    <span v-if="isSubscription(data?.type)" class="br-sale-catalog-option__meta-card is-duration">
                                                        <i class="fa fa-clock" aria-hidden="true"></i>
                                                        <small>Duración</small>
                                                        <strong v-text="data?.formatted_duration" class="text-lowercase"></strong>
                                                    </span>
                                                </div>
                                                <div class="br-sale-catalog-option__codes">
                                                    <small v-if="data?.internal_code">
                                                        <span>Cód. interno</span>
                                                        <strong v-text="data.internal_code"></strong>
                                                    </small>
                                                    <small v-if="data?.barcode">
                                                        <span>Cód. barras</span>
                                                        <strong v-text="data.barcode"></strong>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </v-select>
                            </template>
                        </InputSlot>
                        <InputSlot
                            v-if="selectedModalItem"
                            hasDiv
                            :title="selectedModalCatalogSectionTitle"
                            :titleClass="[config.forms.classes.title, 'colon-at-end']"
                            :isInputGroup="false"
                            :divInputClass="['br-sale-selected-catalog']"
                            xl="12"
                            lg="12">
                            <template v-slot:input>
                                <div class="br-sale-catalog-option br-sale-catalog-option--selected">
                                    <div class="br-sale-catalog-option__line">
                                        <div class="br-sale-catalog-option__meta">
                                            <span class="br-sale-catalog-option__meta-card is-price">
                                                <i class="fa fa-money-bill" aria-hidden="true"></i>
                                                <small>Venta</small>
                                                <strong v-text="`${selectedModalItem?.currency?.sign ?? ''} ${separatorNumber(selectedModalItem?.price)}`"></strong>
                                            </span>
                                            <span v-if="isDefined({value: selectedModalItem?.min_price})" class="br-sale-catalog-option__meta-card is-range is-min">
                                                <i class="fa fa-arrow-down" aria-hidden="true"></i>
                                                <small>Mín.</small>
                                                <strong v-text="`${selectedModalItem?.currency?.sign ?? ''} ${separatorNumber(selectedModalItem?.min_price)}`"></strong>
                                            </span>
                                            <span v-if="isDefined({value: selectedModalItem?.max_price})" class="br-sale-catalog-option__meta-card is-range is-max">
                                                <i class="fa fa-arrow-up" aria-hidden="true"></i>
                                                <small>Máx.</small>
                                                <strong v-text="`${selectedModalItem?.currency?.sign ?? ''} ${separatorNumber(selectedModalItem?.max_price)}`"></strong>
                                            </span>
                                            <span v-if="isSubscription(selectedModalItem?.type)" class="br-sale-catalog-option__meta-card is-duration">
                                                <i class="fa fa-clock" aria-hidden="true"></i>
                                                <small>Duración</small>
                                                <strong v-text="selectedModalItem?.formatted_duration" class="text-lowercase"></strong>
                                            </span>
                                        </div>
                                        <div class="br-sale-catalog-option__codes">
                                            <small v-if="selectedModalItem?.internal_code">
                                                <span>Cód. interno</span>
                                                <strong v-text="selectedModalItem.internal_code"></strong>
                                            </small>
                                            <small v-if="selectedModalItem?.barcode">
                                                <span>Cód. barras</span>
                                                <strong v-text="selectedModalItem.barcode"></strong>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="br-sale-catalog-info-toggle">
                                        <button
                                            type="button"
                                            class="br-link br-link-primary"
                                            @click="selectedCatalogInfoExpanded = !selectedCatalogInfoExpanded"
                                            v-text="selectedCatalogInfoExpanded ? 'Ocultar información adicional' : 'Mostrar información adicional'">
                                        </button>
                                    </div>
                                    <div v-if="selectedCatalogInfoExpanded" class="br-sale-catalog-info">
                                        <div
                                            v-for="detail in selectedModalItemAdditionalDetails"
                                            :key="detail.label"
                                            class="br-sale-catalog-info__item">
                                            <span class="colon-at-end" v-text="detail.label"></span>
                                            <strong v-if="detail.strong" v-text="detail.value"></strong>
                                            <small v-else v-text="detail.value"></small>
                                        </div>
                                    </div>
                                </div>
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
                            :disabled="!selectedModalItem"
                            hasTextBottom
                            :textBottomInfo="forms[entity].createUpdate.extras.modals.details.errors?.price"
                            xl="4"
                            lg="4">
                            <template v-slot:inputGroupPrepend v-if="isDefined({value: forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency})">
                                <span class="input-group-text br-currency-prefix" v-text="forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency?.sign"></span>
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
                                <span class="input-group-text br-currency-prefix" v-text="forms[entity].createUpdate.data.currency?.data?.sign"></span>
                            </template>
                            <template v-slot:input>
                                <input class="form-control" disabled :value="separatorNumber(totalModalDetail)"/>
                            </template>
                        </InputSlot>
                        <InputSlot
                            v-if="false && selectedModalItem"
                            hasDiv
                            :title="MODULE.texts.form.commissionType"
                            :titleClass="[config.forms.classes.title]"
                            xl="6"
                            lg="6">
                            <template v-slot:input>
                                <v-select
                                    v-model="forms[entity].createUpdate.extras.modals.details.data.commission_type"
                                    :options="commissionTypeOptions"
                                    :reduce="option => option.code"
                                    :class="config.forms.classes.select2"
                                    :clearable="false"
                                    :searchable="false"
                                    :disabled="true"
                                    @close="tooltips({show: true, time: 500})"/>
                            </template>
                        </InputSlot>
                        <InputNumber
                            v-if="false && selectedModalItem"
                            v-model="forms[entity].createUpdate.extras.modals.details.data.commission_value"
                            hasDiv
                            :title="MODULE.texts.form.commissionValue"
                            :titleClass="[config.forms.classes.title]"
                            :disabled="true"
                            :minValue="0"
                            :maxValue="forms[entity].createUpdate.extras.modals.details.data.commission_type === 'percentage' ? 100 : null"
                            xl="6"
                            lg="6">
                            <template v-slot:inputGroupPrepend>
                                <span
                                    class="input-group-text br-currency-prefix"
                                    v-text="forms[entity].createUpdate.extras.modals.details.data.commission_type === 'percentage' ? '%' : (forms[entity].createUpdate.extras.modals.details.data.item?.data?.currency?.sign || forms[entity].createUpdate.data.currency?.data?.sign || '')"></span>
                            </template>
                        </InputNumber>
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
                                    <div class="br-sale-membership-duration justify-content-center">
                                        <span class="br-sale-membership-duration__title colon-at-end">Duración total calculada</span>
                                        <div class="br-sale-membership-duration__formula">
                                            <span>
                                                <small>Base</small>
                                                <strong class="text-lowercase" v-text="forms[entity].createUpdate.extras.modals.details.data.extras.formatted_duration"></strong>
                                            </span>
                                            <i>x</i>
                                            <span>
                                                <small>Periodos</small>
                                                <strong class="text-lowercase">
                                                    {{ isDefined({value: forms[entity].createUpdate.extras.modals.details.data.quantity}) ? separatorNumber(forms[entity].createUpdate.extras.modals.details.data.quantity) : '0' }}
                                                </strong>
                                            </span>
                                            <i>=</i>
                                            <span class="is-result">
                                                <small>Resultado</small>
                                                <strong class="text-lowercase" v-text="forms[entity].createUpdate.extras.modals.details.data.extras.formatted_total_duration"></strong>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </InputSlot>
                        </template>
                    </div>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel br-modal-shell__footer-left waves-effect" data-bs-dismiss="modal" v-text="MODULE.texts.actions.close"></button>
                    <button
                        type="button"
                        class="br-btn br-btn-primary br-modal-shell__footer-right waves-effect"
                        :disabled="!selectedModalItem"
                        @click="addDetail()">
                        <span v-text="modalDetailsActionLabel"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.observations.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="MODULE.texts.modal.observations"></h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal" :aria-label="MODULE.texts.actions.close">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body">
                    <InputTextArea
                        v-model="forms[entity].createUpdate.extras.modals.observations.draft"
                        hasDiv
                        :divClass="['p-0']"
                        title=""
                        :placeholder="MODULE.texts.observations.modalPlaceholder"
                        :rows="6"/>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="br-btn br-btn-primary waves-effect" @click="saveObservationsModal">
                        <i class="fa fa-save"></i>
                        <span class="ms-2" v-text="MODULE.texts.actions.saveObservations"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" :id="forms[entity].createUpdate.extras.modals.subscriptions.id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content br-entity-modal br-modal-shell">
                <div class="modal-header br-modal-header br-modal-shell__header">
                    <h5 class="modal-title text-uppercase fw-bold" v-text="MODULE.texts.modal.activeMemberships"></h5>
                    <button type="button" class="br-modal-close br-modal-shell__close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body br-entity-modal__body br-modal-shell__body br-sale-customer-history">
                    <div class="br-sale-customer-history__header">
                        <div class="br-sale-customer-history__avatar" aria-hidden="true">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="br-sale-customer-history__identity">
                            <span class="br-sale-customer-history__eyebrow">Cliente seleccionado</span>
                            <strong v-text="forms[entity].createUpdate.data.holder?.data?.name ?? 'Sin cliente'"></strong>
                            <small v-text="forms[entity].createUpdate.data.holder?.data?.document_number ?? ''"></small>
                        </div>
                        <button
                            type="button"
                            class="br-icon-action br-sale-customer-history__refresh"
                            @click="refreshCustomerHistory()"
                            data-bs-toggle="tooltip"
                            title="Actualizar">
                            <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                        </button>
                    </div>

                    <template v-if="forms[entity].createUpdate.extras.modals.subscriptions.data.loading">
                        <div class="br-sale-customer-history__loader">
                            <Loader/>
                        </div>
                    </template>
                    <template v-else>
                        <div class="br-sale-customer-history__summary">
                            <article>
                                <span><i class="fa-solid fa-receipt" aria-hidden="true"></i> Compras</span>
                                <strong v-text="customerHistorySummary.salesCount"></strong>
                            </article>
                            <article>
                                <span><i class="fa-solid fa-coins" aria-hidden="true"></i> Total comprado</span>
                                <strong v-text="`S/ ${separatorNumber(customerHistorySummary.activeSalesTotal)}`"></strong>
                            </article>
                            <article>
                                <span><i class="fa-solid fa-id-card" aria-hidden="true"></i> Membresías activas</span>
                                <strong v-text="customerHistorySummary.activeSubscriptions"></strong>
                            </article>
                        </div>

                        <div class="row g-3">
                            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                                <section class="br-sale-customer-history__panel">
                                    <header>
                                        <h6>Historial de compras</h6>
                                        <small>Últimos movimientos comerciales del cliente.</small>
                                    </header>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover br-entity-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Comprobante</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="customerHistorySales.length === 0">
                                                    <td colspan="3" class="text-center text-muted py-3">Sin compras registradas.</td>
                                                </tr>
                                                <tr v-for="record in customerHistorySales" :key="`sale-${record.id}`">
                                                    <td v-text="legibleFormatDate({dateString: record.created_at})"></td>
                                                    <td>
                                                        <span v-text="record?.serie?.legible_serie ?? record?.serie?.serie ?? 'Venta'"></span>
                                                        <small v-if="record?.status" class="d-block text-muted text-capitalize" v-text="record.status"></small>
                                                    </td>
                                                    <td class="text-end fw-semibold" v-text="`S/ ${separatorNumber(record.total ?? 0)}`"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                                <section class="br-sale-customer-history__panel">
                                    <header>
                                        <h6>Historial de membresías</h6>
                                        <small>Vigentes y anteriores para revisar continuidad.</small>
                                    </header>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover br-entity-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Inicio</th>
                                                    <th>Fin</th>
                                                    <th class="text-center">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="customerHistorySubscriptions.length === 0">
                                                    <td colspan="3" class="text-center text-muted py-3">Sin membresías registradas.</td>
                                                </tr>
                                                <tr v-for="record in customerHistorySubscriptions" :key="`subscription-${record.id}`">
                                                    <td v-text="legibleFormatDate({dateString: record.start_date})"></td>
                                                    <td v-text="legibleFormatDate({dateString: record.end_date})"></td>
                                                    <td class="text-center">
                                                        <span
                                                            :class="['br-status-label', record.status === 'active' ? 'br-status-active' : 'br-status-inactive']"
                                                            v-text="record.status === 'active' ? 'Activa' : 'Historial'"></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer br-entity-modal__footer br-modal-shell__footer">
                    <button type="button" class="br-btn br-btn-cancel waves-effect" data-bs-dismiss="modal" v-text="MODULE.texts.actions.close"></button>
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
    breadcrumbParent: "Ventas",
    parentMenuId: "menu-parent-sales"
};

const TEXTS = {
    observations: {
        emptyHint: "Sin observaciones para esta venta.",
        discreteEmpty: "Sin observaciones para esta venta.",
        modalPlaceholder: "Escribe aquí la observación de la venta...",
        viewMore: "Ver más",
        viewLess: "Ver menos"
    },
    form: {
        branch: "Sucursal",
        serie: "Tipo de comprobante",
        warehouse: "Almacén",
        issueDate: "Fecha de emisión",
        deliveryMode: "Tipo de entrega",
        holder: "Cliente",
        quotation: "Cotización",
        observation: "Observaciones",
        commercialCatalog: "Catálogo comercial",
        quantity: "Cantidad",
        quantityPeriods: "Cantidad de períodos",
        price: "Precio",
        total: "Total",
        commissionType: "Comisión",
        commissionValue: "Valor de comisión",
        footerTotalLabel: "Importe total:",
        membershipDetail: "Detalle de la membresía",
        detailRowMembershipLabel: "Membresía",
        startDate: "Fecha de inicio",
        endDate: "Fecha de finalización",
        whatsappPlaceholder: "Número con código de país (ej.: 51987654321)",
        emailPlaceholder: "Correo electrónico (ej.: cliente@empresa.com)"
    },
    actions: {
        addDetail: "Agregar ítem",
        generateSale: "Generar venta",
        viewMemberships: "Ver membresias",
        viewCustomerHistory: "Historial del cliente",
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
        observationEdit: "Cambiar observación",
        saveObservations: "Cambiar observación"
    },
    modal: {
        add: "Agregar ítem",
        edit: "Rectificar ítem",
        activeMemberships: "Historial del cliente",
        observations: "Cambiar observación",
        subscriptionOrigin: "Origen",
        subscriptionCreatedAt: "Fecha de creación"
    },
    emptySaleDetailPrefix: "Aún no hay ítems en la venta. Usa ",
    emptySaleDetailSuffix: " para empezar.",
    emptySaleDetailTitle: "Prepara el detalle de la venta",
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
                            commission_type: "none",
                            commission_value: 0,
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
                            mode: "add",
                            edit_index: null
                        },
                        errors: {}
                    },
                    observations: {
                        id: Utils.uuid(),
                        draft: ""
                    },
                    branch: {
                        id: Utils.uuid()
                    },
                    warehouse: {
                        id: Utils.uuid()
                    },
                    delivery: {
                        id: Utils.uuid()
                    },
                    quotation: {
                        id: Utils.uuid()
                    },
                    seller: {
                        id: Utils.uuid()
                    },
                    taxes: {
                        id: Utils.uuid()
                    },
                    payments: {
                        id: Utils.uuid()
                    },
                    subscriptions: {
                        id: Utils.uuid(),
                        titles: {
                            default: TEXTS.modal.activeMemberships
                        },
                        data: {
                            loading: false,
                            tracking: null
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
                delivery_mode: {code: "immediate", label: "Entrega inmediata"},
                holder: null,
                quotation: null,
                quotation_header_id: null,
                seller: null,
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
            observationPreviewExpanded: false,
            selectedCatalogInfoExpanded: false,
            syncingDetailModal: false
        };

    },
    mounted: async function() {

        Utils.navbarItem(MODULE.config.parentMenuId, {addClass: "open"});
        Utils.navbarItem(this.config.entity.page.menu.id, {});
        Alerts.swals({type: "initParams"});

        const initParams = await this.initParams({});
        const initOthers = await this.initOthers({});

        if(initParams && initOthers) {

            const pendingQuotationId = window.sessionStorage.getItem("br_sale_pending_quotation_id");

            if(pendingQuotationId) {

                const quotation = this.quotationOptions.find(record => Number(record.code) === Number(pendingQuotationId));

                if(quotation) {

                    await this.applyQuotationDraft(quotation);

                }

                window.sessionStorage.removeItem("br_sale_pending_quotation_id");

            }

            Alerts.swals({show: false});

        }

    },
    methods: {
        documentTypeLabel(documentType) {

            const value = String(documentType?.name || documentType?.code || "Comprobante").trim().toUpperCase();

            if(value.includes("BOLETA") || value === "BV") return "Boleta";
            if(value === "FACTURA" || value === "FA") return "Factura";

            return value;

        },
        documentTypeTechnicalLabel(documentType) {

            const value = String(documentType?.name || documentType?.code || "Comprobante").trim().toUpperCase();

            if(value.includes("BOLETA") || value === "BV") return "BOLETA";
            if(value === "FACTURA" || value === "FA") return "FACTURA";

            return value;

        },
        holderDocumentTypeLabel(holder = {}) {

            const customer = holder?.data ?? holder;
            const documentType = customer?.identity_document_type;

            if(documentType?.name || documentType?.code) {

                return String(documentType.name || documentType.code).trim().toUpperCase();

            }

            const documentTypeId = customer?.identity_document_type_id;
            const record = (this.options?.customers?.identityDocumentTypes || [])
                .find(type => Number(type.id) === Number(documentTypeId));

            return String(record?.code || record?.name || "Doc.").trim().toUpperCase();

        },
        holderDocumentDescription(holder = {}) {

            const customer = holder?.data ?? holder;
            const documentNumber = customer?.document_number || "S/D";
            const name = customer?.name || "Cliente";

            return `${documentNumber}, ${name}`;

        },
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
        taxQuantityMinimum(tax = {}) {

            return Math.max(1, Number(tax?.min_apply_quantity ?? 1));

        },
        taxQuantityMaximum(tax = {}) {

            return tax?.max_apply_quantity == null ? undefined : Math.max(this.taxQuantityMinimum(tax), Number(tax.max_apply_quantity));

        },
        taxById(taxId) {

            return this.saleTaxes.find(tax => Number(tax.code) === Number(taxId))?.data || {};

        },
        clampTaxQuantity(taxId, quantity) {

            const tax = this.taxById(taxId);
            const minimum = this.taxQuantityMinimum(tax);
            const maximum = this.taxQuantityMaximum(tax);
            const normalized = Math.max(minimum, parseInt(Number(quantity || minimum), 10));

            return maximum === undefined ? normalized : Math.min(normalized, maximum);

        },
        selectedTaxQuantity(taxId) {

            const quantities = this.forms[this.entity].createUpdate.data.selected_tax_quantities || {};

            return this.clampTaxQuantity(taxId, quantities[taxId]);

        },
        normalizeSelectedTaxQuantity(taxId) {

            const quantities = this.forms[this.entity].createUpdate.data.selected_tax_quantities || {};
            quantities[taxId] = this.clampTaxQuantity(taxId, quantities[taxId]);
            this.forms[this.entity].createUpdate.data.selected_tax_quantities = quantities;

        },
        syncSelectedTaxQuantity(tax = {}) {

            if(!this.isFixedTax(tax)) return;

            const quantities = this.forms[this.entity].createUpdate.data.selected_tax_quantities || {};

            if(this.selectedTaxIds().includes(tax.id)) {

                quantities[tax.id] = this.taxQuantityMinimum(tax);

            }else {

                quantities[tax.id] = 0;

            }

            this.forms[this.entity].createUpdate.data.selected_tax_quantities = quantities;

        },
        openTaxesModal() {

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.taxes.id});

        },
        selectedTaxIds() {

            return this.forms[this.entity].createUpdate.data.selected_taxes || [];

        },
        async applyQuotationDraft(quotation) {

            if(!quotation?.code) return;

            Alerts.swals({type: "loading", message: "Cargando cotización"});

            const result = await Requests.get({
                route: `${Requests.config({entity: "quotations", type: "consult"})}/${quotation.code}/sale-draft`
            });

            Alerts.swals({show: false});

            if(!Requests.valid({result})) {
                Alerts.generateAlert({type: "error", msgContent: result.data?.msg || "No se pudo cargar la cotización."});
                return;
            }

            const draft = result.data.data;
            const form = this.forms[this.entity].createUpdate.data;

            form.quotation = quotation;
            form.quotation_header_id = draft.quotation_header_id;
            form.branch = this.branches.find(branch => Number(branch.code) === Number(draft.branch_id)) || form.branch;
            form.holder = this.holders.find(holder => Number(holder.code) === Number(draft.holder_id)) || form.holder;
            form.currency = this.currencies.find(currency => Number(currency.code) === Number(draft.currency_id)) || form.currency;
            form.observation = draft.observation || form.observation;
            form.details = (draft.details || []).map(detail => {
                const option = this.items.find(item => Number(item.code) === Number(detail.item_id));
                const itemData = option?.data || {};
                const currency = itemData.currency || form.currency?.data || null;

                return {
                    id: Utils.uuid(),
                    item: option || {code: detail.item_id, label: detail.name, data: itemData},
                    type: detail.type,
                    currency,
                    name: detail.name,
                    quantity: detail.quantity,
                    price: detail.price,
                    price_includes_tax: detail.price_includes_tax,
                    commission_type: itemData.commission_type || "none",
                    commission_value: Number(itemData.commission_value || itemData.commission_rate || 0),
                    commission_amount: 0,
                    observation: detail.recalculated_from_quote
                        ? "Precio recalculado desde la cotización."
                        : (detail.observation || ""),
                    extras: {
                        min_price: itemData.min_price ?? "",
                        max_price: itemData.max_price ?? "",
                        duration_type: itemData.duration_type ?? "",
                        duration_value: itemData.duration_value ?? "",
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
                };
            });

            form.payments = [this.newSalePayment({amount: this.total})];
            Alerts.generateAlert({
                type: "success",
                msgContent: "Cotización cargada con precios vigentes."
            });

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
            this.options.quotations = initParams.data?.config?.quotations;
            this.options.users = initParams.data?.config?.users;

            return Requests.valid({result: initParams});

        },
        async initOthers({}) {

            return new Promise(resolve => {

                this.forms[this.entity].createUpdate.data.branch     = (this.branches).length > 0 ? this.branches[0] : null;
                this.forms[this.entity].createUpdate.data.warehouse  = (this.warehouses).length > 0 ? this.warehouses[0] : null;
                this.forms[this.entity].createUpdate.data.issue_date = Utils.getCurrentDate();
                this.forms[this.entity].createUpdate.data.holder     = (this.holders).length > 0 ? this.holders[0] : null;
                this.forms[this.entity].createUpdate.data.seller     = this.defaultSellerOption;
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
        openBranchModal() {

            if(!this.canChangeBranch) return;

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.branch.id});

        },
        openWarehouseModal() {

            if(!this.forms[this.entity].createUpdate.data.warehouse && this.warehouses.length) {

                this.forms[this.entity].createUpdate.data.warehouse = this.warehouses[0];

            }

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.warehouse.id});

        },
        openDeliveryModal() {

            if(!this.forms[this.entity].createUpdate.data.delivery_mode) {

                this.forms[this.entity].createUpdate.data.delivery_mode = this.deliveryModes[0];

            }

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.delivery.id});

        },
        openQuotationModal() {

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.quotation.id});

        },
        openSellerModal() {

            if(!this.forms[this.entity].createUpdate.data.seller) {

                this.forms[this.entity].createUpdate.data.seller = this.defaultSellerOption;

            }

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.seller.id});

        },
        clearQuotation() {

            const form = this.forms[this.entity].createUpdate.data;

            form.quotation = null;
            form.quotation_header_id = null;

            window.sessionStorage.removeItem("br_sale_pending_quotation_id");
            Alerts.generateAlert({
                type: "warning",
                msgContent: "Cotización retirada de la venta."
            });

        },
        newSalePayment({amount = ""} = {}) {

            const payment = {
                key: Utils.uuid(),
                method: this.salePaymentMethods.find(method => method.data?.is_default) || this.salePaymentMethods[0] || null,
                variant: null,
                amount,
                reference: "",
                note: ""
            };

            this.syncSalePaymentVariant(payment);

            return payment;

        },
        paymentAssetUrl(record) {

            const path = record?.image_path || record?.data?.image_path;

            if(!path) return "";

            if(/^https?:\/\//i.test(path) || path.startsWith("/")) return path;

            return `/${path}`;

        },
        salePaymentVariantOptions(payment) {

            const method = payment?.method?.data || {};
            const variants = method.supports_variants ? (method.variants || []) : [];

            return variants
                .filter(variant => variant.status !== "inactive")
                .map(variant => ({
                    code: variant.id,
                    label: variant.name,
                    data: variant
                }));

        },
        syncSalePaymentVariant(payment) {

            if(!payment) return;

            const options = this.salePaymentVariantOptions(payment);

            if(options.length === 0) {
                payment.variant = null;
                return;
            }

            const current = options.find(option => Number(option.code) === Number(payment.variant?.code));
            payment.variant = current || options.find(option => option.data?.is_default) || options[0];

        },
        paymentRequiresReference(payment) {

            return Boolean(payment?.variant?.data?.requires_reference ?? payment?.method?.data?.requires_reference);

        },
        addSalePayment() {

            const pending = this.salePaymentDifference > 0 ? this.salePaymentDifference : "";

            this.forms[this.entity].createUpdate.data.payments.push(this.newSalePayment({amount: pending}));

        },
        removeSalePayment(index) {

            if(this.forms[this.entity].createUpdate.data.payments.length <= 1) return;

            this.forms[this.entity].createUpdate.data.payments.splice(index, 1);

        },
        openPaymentMethodsModal() {

            if((this.forms[this.entity].createUpdate.data.payments || []).length === 0) {

                this.forms[this.entity].createUpdate.data.payments = [this.newSalePayment({amount: this.total})];

            }

            Alerts.modals({type: "show", id: this.forms[this.entity].createUpdate.extras.modals.payments.id});

        },
        // Actions modal detail
        modalAddDetail({}) {

            let form = this.forms[this.entity].createUpdate.extras.modals.details;

            form.data.mode = "add";
            form.data.edit_index = null;

            Alerts.modals({type: "show", id: form.id});

        },
        modalEditDetail({record, keyRecord}) {

            this.resetActionTooltips();

            const form = this.forms[this.entity].createUpdate.extras.modals.details;
            const itemCode = record?.item?.code ?? record?.item_id ?? record?.item?.id;
            const itemOption = (this.items || []).find(item => Number(item.code) === Number(itemCode)) || record?.item || null;

            this.syncingDetailModal = true;
            form.errors = {};
            form.data = {
                ...Utils.cloneJson(record),
                item: itemOption,
                mode: "update",
                edit_index: keyRecord
            };
            this.selectedCatalogInfoExpanded = false;

            this.$nextTick(() => {
                this.syncingDetailModal = false;
                Alerts.modals({type: "show", id: form.id});
            });

        },
        addDetail() {

            const functionName = "addDetail";

            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms[this.entity].createUpdate.extras.modals.details.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                const detailIndex = Number(form.edit_index);

                delete form.edit_index;
                delete form.item.data;

                if(["add"].includes(form.mode)) {

                    (this.forms[this.entity].createUpdate.data.details).push({...form, id: Utils.uuid()});

                    // Alerts.generateAlert({type: "success", msgContent: `Se ha agregado <b><small>(${form?.quantity})</small> ${form?.name}</b> al detalle de la venta.`});
                    Alerts.toastrs({type: "success", subtitle: `Se ha agregado <b><small>(${form?.quantity})</small> ${form?.name}</b> al detalle de la venta.`});

                    this.clearForm({functionName});
                    this.$nextTick(() => this.resetActionTooltips());

                }

                if(["update"].includes(form.mode)) {

                    const details = this.forms[this.entity].createUpdate.data.details;

                    if(Number.isInteger(detailIndex) && details[detailIndex]) {

                        details.splice(detailIndex, 1, {...form, id: form.id || details[detailIndex].id || Utils.uuid()});

                        Alerts.toastrs({type: "success", subtitle: `<b>${form?.name}</b> ha sido rectificado en el detalle de la venta.`});

                        this.clearForm({functionName});
                        Alerts.modals({type: "hide", id: this.forms[this.entity].createUpdate.extras.modals.details.id});
                        this.$nextTick(() => this.resetActionTooltips());

                    }

                }

            }else {

                // this.formErrors({functionName, type: "set", errors: validateForm});
                // Alerts.toastrs({type: "error", subtitle: this.config.messages.errorValidate});
                Alerts.generateAlert({messages: Utils.getErrors({errors: validateForm}), msgContent: `<div class="fw-semibold mb-2">${this.config.messages.errorValidate}</div>`});

            }

        },
        changeQuantityDetail({record, keyRecord, type = "add"}) {

            this.resetActionTooltips();

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
        clearDetails() {

            const form = this.forms[this.entity].createUpdate.data;

            if(!form.details.length) {

                Alerts.generateAlert({
                    type: "warning",
                    msgContent: "No hay ítems para limpiar en el detalle de la venta."
                });
                return;

            }

            Swal.fire({
                html: "<span>¿Desea limpiar todos los ítems del detalle de la venta?</span>",
                icon: "warning",
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonText: "Sí, limpiar",
                cancelButtonText: "Cancelar",
                customClass: {
                    confirmButton: "btn btn-danger waves-effect",
                    cancelButton: "btn btn-secondary waves-effect ms-3"
                }
            }).then(result => {

                if(!result.isConfirmed) return;

                form.details = [];
                form.payments = [this.newSalePayment({amount: ""})];

                Alerts.generateAlert({
                    type: "success",
                    msgContent: "Detalle de venta limpiado correctamente."
                });

            });

        },
        deleteDetail({record, keyRecord}) {

            this.resetActionTooltips();

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

            this.resetActionTooltips();

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

            this.resetActionTooltips();

            record.extras.showDetail = !record.extras.showDetail;

        },
        resetActionTooltips() {

            Alerts.tooltips({show: false, time: 0});
            setTimeout(() => Alerts.tooltips({show: true}), 140);

        },
        normalizeSubscriptionsResponse(response) {

            if(!Requests.valid({result: response})) {

                return [];

            }

            return response?.data?.data?.subscriptions ?? response?.data?.subscriptions ?? [];

        },
        viewSubscriptions({}) {

            this.viewCustomerHistory();

        },
        viewCustomerHistory() {

            const holder = this.forms[this.entity].createUpdate.data.holder?.data;

            if(!holder?.id) {

                Alerts.toastrs({type: "warning", subtitle: "Seleccione un cliente para ver su historial."});
                return;

            }

            const form = this.forms[this.entity].createUpdate.extras.modals.subscriptions;

            this.refreshCustomerHistory();

            Alerts.modals({type: "show", id: form.id});

        },
        async refreshSubscriptions() {

            return this.refreshCustomerHistory();

        },
        async refreshCustomerHistory() {

            const form = this.forms[this.entity].createUpdate.extras.modals.subscriptions;
            const holder = this.forms[this.entity].createUpdate.data.holder?.data;

            if(!holder?.id) {

                return;

            }

            form.data.loading = true;

            Alerts.tooltips({show: false, time: 0});

            try {

                const [subscriptionsResponse, trackingResponse] = await Promise.all([
                    Utils.getSubscriptions({customer: holder}),
                    Utils.getTrackingCustomers({
                        customer: holder,
                        period_type: "last_12_months",
                        options: {information: ["sales", "subscriptions"]}
                    })
                ]);

                this.options.holders.subscriptions[holder.id] = this.normalizeSubscriptionsResponse(subscriptionsResponse);
                form.data.tracking = Requests.valid({result: trackingResponse}) ? (trackingResponse?.data?.tracking ?? null) : null;

            }finally {

                form.data.loading = false;
                Alerts.tooltips({show: true});

            }

        },
        // Entity forms
        async createUpdateEntity() {

            const functionName = "createUpdateEntity";

            if(this.saleSubmitBlocker) {

                Alerts.toastrs({type: "warning", subtitle: this.saleSubmitBlocker.message});
                return;

            }

            Alerts.swals({});
            this.formErrors({functionName, type: "clear"});

            let form = Utils.cloneJson(this.forms[this.entity].createUpdate.data);

            const validateForm = this.validateForm({functionName, form, extras: {type: "descriptive"}});

            if(validateForm?.bool) {

                form.branch_id   = form?.branch?.code;
                form.serie_id    = form?.serie?.code;
                form.warehouse_id = this.saleRequiresStockContext ? form?.warehouse?.code : null;
                form.holder_id   = form?.holder?.code;
                form.seller_id   = form?.seller?.code;
                form.currency_id = form?.currency?.code;
                form.delivery_mode = this.saleRequiresStockContext ? (form?.delivery_mode?.code || "immediate") : null;
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
                delete form.seller;
                delete form.currency;
                delete form.quotation;
                delete form.delivery_status;
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
                    this.forms[this.entity].createUpdate.extras.modals.details.data.mode = "add";
                    this.forms[this.entity].createUpdate.extras.modals.details.data.edit_index = null;
                    break;

                case "createUpdateEntity":
                    this.forms[this.entity].createUpdate.data.id          = null;
                    // this.forms[this.entity].createUpdate.data.issue_date  = Utils.getCurrentDate();
                    // this.forms[this.entity].createUpdate.data.holder      = null;
                    this.forms[this.entity].createUpdate.data.observation = "";
                    this.forms[this.entity].createUpdate.data.quotation = null;
                    this.forms[this.entity].createUpdate.data.quotation_header_id = null;
                    this.forms[this.entity].createUpdate.data.seller = this.defaultSellerOption;
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

                if(this.saleRequiresStockContext && !this.isDefined({value: form?.warehouse})) {

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
        saleDetailUsesStock(detail) {

            return ["product"].includes(String(detail?.type || "").toLowerCase());

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
        formatSaleDetailDateTime(value = null) {

            if(!this.isDefined({value})) return "Sin fecha";

            return this.legibleFormatDate({dateString: value, type: "datetime"}) || String(value).replace("T", " ");

        },
        tooltips({show = true, time = 10}) {

            Alerts.tooltips({show, time});

        },
        itemTypeLabel(item = {}) {

            if(item?.type === "service") return "Servicio";
            if(item?.type === "subscription") return "Membresía";
            if(item?.type === "recipe" || item?.type === "dish") return "Platillo";

            return "Producto";

        },
        booleanCatalogLabel(value) {

            return [true, 1, "1", "true"].includes(value) ? "Sí" : "No";

        },
        catalogCategoriesLabel(item = {}) {

            const links = item?.category_items || item?.categoryItems || item?.categories || [];
            const names = links
                .map(categoryLink => categoryLink?.category?.name || categoryLink?.name || categoryLink?.label)
                .filter(Boolean);

            return names.length ? names.join(", ") : "Sin categorías";

        },
        catalogCommissionLabel(item = {}) {

            const type = item?.commission_type || (Number(item?.commission_rate || 0) > 0 ? "percentage" : "none");
            const value = Number(item?.commission_value ?? item?.commission_rate ?? 0);

            if(type === "none" || value <= 0) return "Sin comisión configurada";

            if(type === "percentage") return `Porcentaje: ${this.separatorNumber(value)}%`;

            const sign = item?.currency?.sign || this.forms[this.entity].createUpdate.data.currency?.data?.sign || "";

            return `Monto fijo: ${sign} ${this.separatorNumber(value)}`;

        },
        catalogCapacityLabel(item = {}) {

            if(![true, 1, "1", "true"].includes(item?.capacity_control_enabled)) {

                return "Sin control de cupos";

            }

            const limit = Number(item?.capacity_limit || 0);
            const used = Number(item?.capacity_used || 0);
            const available = item?.available_capacity ?? Math.max(0, limit - used);

            return `${available} disponibles de ${limit}`;

        },
        catalogExpirationLabel(item = {}) {

            if(!item?.expires_at) return "Sin fecha de vencimiento";

            return this.legibleFormatDate({dateString: item.expires_at, type: "date"}) || "Sin fecha de vencimiento";

        },
        catalogTextValue(value, fallback = "No registrado") {

            if(Array.isArray(value)) {

                const normalized = value
                    .map(row => typeof row === "string" ? row : (row?.label || row?.name || row?.description))
                    .filter(Boolean);

                return normalized.length ? normalized.join(", ") : fallback;

            }

            if(value === null || value === undefined || String(value).trim() === "") return fallback;

            return String(value).trim();

        },
        async sendWhatsapp({data = null, action = "reportSale"}) {

            const phoneNumber = this.forms[this.entity].createUpdate.extras.modals.finished.data.whatsapp;
            const url = await Requests.saleReportShareUrl({document: data?.id, type: "a4"});
            const message = Utils.getMessageWhatsapp({data, action, url});

            Utils.sendWhatsapp({phoneNumber, message});

        },
        async sendEmail({data = null, action = "reportSale"}) {

            let route = Requests.config({entity: "helpers", type: "sendEmail"});
            const url = await Requests.saleReportShareUrl({document: data?.id, type: "a4"});
            const formJson = {id: data?.id, serie_sequential: data?.serie_sequential, email: data?.email, message: Utils.getMessageWhatsapp({data, action, url})};

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
        users() {

            return (this.options?.users?.records || []).map(record => ({
                code: record.id,
                label: record.name,
                data: record
            }));

        },
        defaultSellerOption() {

            const currentUserId = Number(this.options?.users?.current_id || 0);

            return this.users.find(record => Number(record.code) === currentUserId) || this.users[0] || null;

        },
        customerHistoryTracking() {

            return this.forms[this.entity].createUpdate.extras.modals.subscriptions.data.tracking ?? {};

        },
        customerHistorySales() {

            return this.customerHistoryTracking.sales ?? [];

        },
        customerHistorySubscriptions() {

            const holderId = this.forms[this.entity].createUpdate.data.holder?.data?.id;
            const trackingSubscriptions = this.customerHistoryTracking.subscriptions ?? [];
            const activeSubscriptions = this.options?.holders?.subscriptions?.[holderId] ?? [];
            const records = new Map();

            [...activeSubscriptions, ...trackingSubscriptions].forEach(record => {

                if(record?.id !== undefined && record?.id !== null) {

                    records.set(record.id, record);

                }

            });

            return Array.from(records.values());

        },
        customerHistorySummary() {

            const summary = this.customerHistoryTracking.summary ?? {};

            return {
                salesCount: Number(summary.sales_count ?? this.customerHistorySales.length ?? 0),
                activeSalesTotal: Number(summary.active_sales_total ?? 0),
                activeSubscriptions: Number(summary.active_subscriptions ?? this.customerHistorySubscriptions.filter(record => record?.status === "active").length ?? 0)
            };

        },
        branches: function() {

            return (this.options?.branches?.records || []).map(e => ({code: e.id, label: e.name, data: e}));

        },
        series: function() {

            const branch = (this.options?.branches?.records ?? []).filter(e => e?.id == this.forms[this.entity].createUpdate.data.branch?.code);

            if(branch.length === 1) {

                const series = (branch[0].series ?? []).filter(e => e?.status === "active");

                return series.map(e => ({
                    code: e.id,
                    label: this.documentTypeLabel(e?.document_type),
                    description: `Serie ${e.legible_serie}`,
                    data: e
                }));

            }

            return [];

        },
        warehouses: function() {

            const branchId = this.forms[this.entity].createUpdate.data.branch?.code;

            return (this.options?.warehouses?.records ?? [])
                .filter(e => e?.status === "active" && (!branchId || e?.branch_id == branchId))
                .map(e => ({
                    code: e.id,
                    label: e.name,
                    data: e
                }));

        },
        deliveryModes() {

            return [
                {code: "immediate", label: "Entrega inmediata", description: "Descuenta el stock al generar la venta."},
                {code: "pending", label: "Entrega pendiente", description: "La venta queda registrada y el stock se descuenta al entregar."}
            ];

        },
        canChangeBranch() {

            return (this.branches || []).length > 1;

        },
        canChangeWarehouse() {

            return (this.warehouses || []).length > 1;

        },
        canChangeTaxes() {

            return true;

        },
        canChangePaymentMethods() {

            return true;

        },
        branchLabel() {

            return this.forms[this.entity].createUpdate.data.branch?.label || "Seleccione sucursal";

        },
        deliveryModeLabel() {

            return this.forms[this.entity].createUpdate.data.delivery_mode?.label || "Entrega inmediata";

        },
        sellerLabel() {

            return this.forms[this.entity].createUpdate.data.seller?.label || "Seleccione vendedor";

        },
        hasQuotationApplied() {

            return Boolean(this.forms[this.entity].createUpdate.data.quotation_header_id);

        },
        quotationSummaryLabel() {

            const quotation = this.forms[this.entity].createUpdate.data.quotation?.data || {};
            const holder = quotation?.holder?.name || this.forms[this.entity].createUpdate.data.holder?.data?.name || "Cliente";
            const total = quotation?.total != null ? ` · S/ ${this.separatorNumber(quotation.total)}` : "";

            return `${holder}${total}`;

        },
        warehouseLabel() {

            return this.forms[this.entity].createUpdate.data.warehouse?.label || "Seleccione almacén";

        },
        saleConfigurationIssue() {

            const branch = this.forms[this.entity].createUpdate.data.branch;

            if(!branch) return null;

            if(!this.series.length) {

                return {
                    title: "Falta una serie activa",
                    message: "Crea o activa una serie para esta sucursal antes de generar la venta."
                };

            }

            if(this.saleRequiresStockContext && !this.warehouses.length) {

                return {
                    title: "Falta un almacén activo",
                    message: "Crea o activa un almacén para esta sucursal antes de generar la venta."
                };

            }

            return null;

        },
        saleHasDetails() {

            return (this.forms[this.entity].createUpdate.data.details || []).length > 0;

        },
        saleIsCredit() {

            return ["cash_on_delivery", "installments"].includes(this.forms[this.entity].createUpdate.data.payment_modality);

        },
        saleHasValidPayments() {

            const payments = this.forms[this.entity].createUpdate.data.payments || [];

            return payments.some(payment => payment.method?.code && Number(payment.amount || 0) > 0);

        },
        salePaymentIsBalanced() {

            return Math.abs(Number(this.salePaymentDifference || 0)) < 0.000001;

        },
        saleSubmitBlocker() {

            if(this.saleConfigurationIssue) return this.saleConfigurationIssue;

            const form = this.forms[this.entity].createUpdate.data;

            if(!this.saleHasDetails) {

                return {
                    title: "Falta detalle",
                    message: "Agrega al menos un ítem a la venta."
                };

            }

            if(Number(this.total || 0) <= 0) {

                return {
                    title: "Total inválido",
                    message: "El total de la venta debe ser mayor a cero."
                };

            }

            if(!this.isDefined({value: form?.holder})) {

                return {
                    title: "Falta cliente",
                    message: "Selecciona un cliente para generar la venta."
                };

            }

            if(!this.saleIsCredit && !this.saleHasValidPayments) {

                return {
                    title: "Falta método de pago",
                    message: "Configura al menos un método de pago con monto mayor a cero."
                };

            }

            if(!this.saleIsCredit && !this.salePaymentIsBalanced) {

                return {
                    title: "Pago incompleto",
                    message: "El monto pagado debe coincidir con el total de la venta."
                };

            }

            return null;

        },
        holders: function() {

            return this.options?.holders?.records.map(e => ({code: e.id, label: `${e.document_number} - ${e.name}`, data: e}));

        },
        currencies: function() {

            return this.options?.currencies?.records.map(e => ({code: e.id, label: e.plural_name, data: e}));

        },
        items: function() {

            return this.options?.items?.records.map(e => ({
                code: e.id,
                label: e.name,
                data: e
            }));

        },
        quotationOptions() {

            return (this.options?.quotations?.records || []).map(record => ({
                code: record.id,
                label: `${record.reference} - ${record.holder?.name || "Cliente"} - S/ ${this.separatorNumber(record.total)}`,
                data: record
            }));

        },
        commissionTypeOptions() {

            return [
                {code: "none", label: "Sin comisión"},
                {code: "percentage", label: "Porcentaje"},
                {code: "fixed", label: "Monto fijo por unidad"}
            ];

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
        saleOptionalTaxSummary() {

            const selected = this.selectedTaxIds();

            return (this.optionalSaleTaxes || [])
                .filter(tax => selected.includes(tax.code))
                .map(tax => {

                    const line = this.calculateSaleTaxLine(tax.data || {});

                    return {
                        key: tax.code,
                        name: line.name,
                        description: this.isFixedTax(tax.data) ? `${line.quantity} vez${Number(line.quantity) === 1 ? "" : "es"} × ${this.taxLabel(tax.data)}` : this.taxLabel(tax.data),
                        amount: line.amount
                    };

                });

        },
        salePaymentMethods() {

            return (this.options?.paymentMethods?.records || []).map(method => ({
                code: method.id,
                label: method.name,
                data: method
            }));

        },
        salePaymentSummary() {

            return (this.forms[this.entity].createUpdate.data.payments || [])
                .filter(payment => payment.method?.code)
                .map(payment => ({
                    key: payment.key,
                    label: payment.variant?.label || payment.method?.label || "Método de pago",
                    reference: payment.reference || "",
                    amount: Number(payment.amount || 0)
                }));

        },
        saleRequiresStockContext() {

            return (this.forms[this.entity].createUpdate.data.details || [])
                .some(detail => this.saleDetailUsesStock(detail));

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
                    payment_method_variant_id: payment.variant?.code || null,
                    name: payment.variant?.label || payment.method?.label || null,
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

            return `${full.slice(0, max)}...`;

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
        selectedModalItem() {

            return this.forms[this.entity].createUpdate.extras.modals.details.data.item?.data || null;

        },
        selectedModalCatalogSectionTitle() {

            return this.itemTypeLabel(this.selectedModalItem);

        },
        selectedModalItemAdditionalDetails() {

            const item = this.selectedModalItem;

            if(!item) return [];

            const details = [
                {label: "Tipo", value: this.itemTypeLabel(item), strong: true},
                {label: "Marca", value: item?.brand?.name || "Sin marca"},
                {label: "Categorías", value: this.catalogCategoriesLabel(item)},
                {label: "Descripción", value: this.catalogTextValue(item?.description, "Sin descripción")},
                {label: "Incluye IGV", value: this.booleanCatalogLabel(item?.price_includes_tax)},
                {label: "Cupos", value: this.catalogCapacityLabel(item)},
                {label: "Vencimiento", value: this.catalogExpirationLabel(item)},
                {label: "Comisión", value: this.catalogCommissionLabel(item)}
            ];

            if(item?.estimated_duration_minutes) {

                details.push({label: "Tiempo estimado", value: `${item.estimated_duration_minutes} min`});

            }

            if(this.isSubscription(item?.type) && item?.formatted_duration) {

                details.push({label: "Duración", value: item.formatted_duration});

            }

            if(item?.benefits) {

                details.push({label: "Beneficios", value: this.catalogTextValue(item.benefits, "Sin beneficios")});

            }

            if(item?.restrictions) {

                details.push({label: "Restricciones", value: this.catalogTextValue(item.restrictions, "Sin restricciones")});

            }

            if(item?.see_my_web !== undefined || item?.see_my_web_price !== undefined) {

                details.push({
                    label: "Catálogo externo",
                    value: `${this.booleanCatalogLabel(item?.see_my_web)}${item?.see_my_web_price !== undefined ? ` · precio visible: ${this.booleanCatalogLabel(item.see_my_web_price)}` : ""}`
                });

            }

            return details;

        },
        modalDetailsTitle() {

            const mode   = this.forms[this.entity].createUpdate.extras.modals.details.data?.mode;
            const titles = this.forms[this.entity].createUpdate.extras.modals.details.titles;

            return titles?.[mode] ?? "";

        },
        modalDetailsActionLabel() {

            return this.isModalDetailUpdate ? "Rectificar" : this.MODULE.texts.actions.add;

        },
        isModalDetailUpdate() {

            return this.forms[this.entity].createUpdate.extras.modals.details.data?.mode === "update";

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

            const form = this.forms[this.entity].createUpdate.extras.modals.subscriptions;
            form.data.tracking = null;

            if(!newValue?.data?.id) {

                return;

            }

            const getSubscriptions = await Utils.getSubscriptions({customer: newValue?.data});

            this.options.holders.subscriptions[newValue.data.id] = this.normalizeSubscriptionsResponse(getSubscriptions);

        },
        "forms.sales.createUpdate.extras.modals.details.data.item": function(newValue) {

            if(this.syncingDetailModal) return;

            const data = newValue?.data;

            this.selectedCatalogInfoExpanded = false;

            const modalData = this.forms[this.entity].createUpdate.extras.modals.details.data;

            modalData.type     = data?.type;
            modalData.currency = data?.currency;
            modalData.name     = data?.name;
            modalData.price    = Number(data?.price ?? 0);
            modalData.commission_type = data?.commission_type || (Number(data?.commission_rate || 0) > 0 ? "percentage" : "none");
            modalData.commission_value = Number(data?.commission_value ?? data?.commission_rate ?? 0);

            if(modalData.commission_type === "none") {

                modalData.commission_value = 0;

            }

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
