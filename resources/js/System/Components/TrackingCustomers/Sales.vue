<template>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="text-center align-middle">
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 25%;">DOCUMENTO</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 25%;">FECHA DE EMISIÓN</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 25%;">TOTAL</th>
                    <th class="bg-secondary text-white fw-semibold min-w-150px" style="width: 15%;">ESTADO</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0 bg-white">
                <template v-if="data.length > 0">
                    <tr v-for="record in data" :key="record.id" class="text-center">
                        <td class="text-start">
                            <span v-text="record.serie_sequential" class="fw-bold d-block"></span>
                            <small v-text="record.serie?.document_type?.name" class="d-block"></small>
                        </td>
                        <td>
                            <span v-text="record.formatted_issue_date" class="d-block"></span>
                            <span :class="['badge', 'fw-semibold', 'mt-1', { 'bg-label-success': record.diff_days_issue_date == 0, 'bg-label-warning': record.diff_days_issue_date != 0 }]" v-text="diffDaysLegible({diff: record.diff_days_issue_date})"></span>
                        </td>
                        <td>
                            <span v-text="record.currency?.sign ?? ''" class="fw-semibold"></span>
                            <span v-text="separatorNumber(record.total)" class="fw-semibold ms-1"></span>
                        </td>
                        <td>
                            <span :class="['badge', 'fw-semibold', 'text-capitalize', { 'bg-label-success': ['active'].includes(record.status), 'bg-label-danger': ['inactive', 'canceled'].includes(record.status) }]" v-text="record.formatted_status"></span>
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
</template>

<script>
import * as Utils  from "../../Helpers/Utils.js";

export default {
    name: "Sales",
    emits: [],
    props: {
        data: {
            type: Array,
            required: false,
            default: []
        }
    },
    computed: {
        //
    },
    methods: {
        // Others
        isDefined({value}) {

            return Utils.isDefined({value});

        },
        legibleFormatDate({dateString = null, type = "datetime"}) {

            return Utils.legibleFormatDate({dateString, type});

        },
        separatorNumber(value) {

            return Utils.separatorNumber(value);

        },
        diffDaysLegible({diff}) {

            return Utils.diffDaysLegible({diff});

        }
    }
};
</script>
