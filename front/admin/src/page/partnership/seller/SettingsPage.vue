<template>
    <div class="partnershipSellerSettingsPage">
        <form-type-form v-if="form" :form="form">
            <v-radio-group v-model="form.data.partnerSellActionId"
                           :error-messages="form.errors.partnerSellActionId"
                           label="При продаже товара через НЕ партнера"
                           dense
            >
                <v-radio v-for="(item,i) in form.definition.fields.partnerSellActionId.choices"
                         :key="i"
                         :label="item.text"
                         :value="item.value"
                />
            </v-radio-group>
            <v-text-field v-model="form.data.partnerDefaultFee"
                          :error-messages="form.errors.partnerDefaultFee"
                          :disabled="form.data.partnerSellActionId!=='3'"
                          label="Комиссия(%)"
            />
        </form-type-form>
        <card-loading v-else/>
    </div>
</template>

<script>
    import {page} from './../../../mixin/page';
    import CardLoading from "../../../component/CardLoading";
    import FormTypeForm from "../../../form/FormTypeForm";

    export default {
        mixins: [page],
        components: {CardLoading, FormTypeForm},
        data() {
            return {
                config: config,
                form: null,
            }
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.$ewll.initForm(this, {
                    method: 'get',
                    url: `/crud/partnershipSellerSettings/${this.config.user.id}/form`,
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: `/crud/partnershipSellerSettings/${this.config.user.id}`,
                            definition: response.body,
                            data: response.body.data,
                            snackSuccessMessage: 'Сохранено',
                        });
                    }.bind(this),
                }).submit();
            },
        }
    }
</script>

<style>
</style>
