<template>
    <v-dialog v-model="dialog" width="600">
        <template v-slot:activator="{ on }">
            <icon-button-with-progress
                    @action="open"
                    :loading="dataForm.loading"
                    icon="mdi-handshake"
                    tooltip="Предложить сотрудничество"
            />
        </template>
        <form-type-form v-if="data" :form="offerForm" :title="'Предложить сотрудничество '+data.name">
            <div style="white-space:pre">{{data.agentInfo}}</div>
            <div>
                <v-text-field :error-messages="offerForm.errors.fee"
                              v-model="offerForm.data.fee"
                              label="Дополнительная комиссия(%)"
                />
            </div>
            <template v-slot:actions>
                <v-btn @click.native="dialog=false" text>Закрыть</v-btn>
                <v-spacer/>
                <v-btn color="primary" @click="offer" :disabled="offerForm.loading" text>
                    Предложить сотрудничество
                </v-btn>
            </template>
        </form-type-form>
    </v-dialog>
</template>

<script>
    import IconButtonWithProgress from './../../../../component/IconButtonWithProgress';
    import FormTypeForm from './../../../../form/FormTypeForm';

    export default {
        components: {IconButtonWithProgress, FormTypeForm},
        props: {
            id: Number,
        },
        data() {
            return {
                dataForm: null,
                data: null,
                dialog: false,
                offerForm: null,
            }
        },
        created() {
            this.dataForm = this.$ewll.initForm(this, {
                method: 'get',
                url: '/crud/partnershipSellerSearchAgent/' + this.id,
                success: function (response) {
                    this.data = response.body;
                    this.dialog = true;
                }.bind(this),
            });
            this.offerForm = this.$ewll.initForm(this, {
                url: '/crud/partnershipSellerSearchAgent/offer',
                hiddenFields: {agentUserId: this.id},
                snackSuccessMessage: 'Предложение отправлено',
                success: function () {
                    this.dialog = false;
                    this.$emit('offered');
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
            },
            open() {
                this.dataForm.submit();
            },
            offer() {
                this.offerForm.submit();
            },
        }
    }
</script>

<style>
</style>
