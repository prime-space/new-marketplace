<template>
    <div class="partnershipSellerAgentPage">
        <form-type-form v-if="form && item" :form="form">
            <div class="subtitle-1">Связаться</div>
            <div v-if="item.contactTypeId===1">
                <v-icon>mdi-telegram</v-icon>
                <a :href="'https://t.me/'+item.contact" target="_blank"> {{item.contact}}</a>
            </div>
            <div v-else-if="item.contactTypeId===2">
                <v-icon>mdi-skype</v-icon>
                <a :href="'skype:'+item.contact+'?chat'"> {{item.contact}}</a>
            </div>
            <br>
            <div class="subtitle-1">Редактировать</div>
            <v-text-field :error-messages="form.errors.fee"
                          v-model="form.data.fee"
                          label="Дополнительная комиссия(%)"
            />
            <template v-slot:actions>
                <v-btn @click.native="terminate" color="error" :disabled="terminateForm.loading" text>
                    Расторгнуть партнерство
                </v-btn>
                <v-spacer/>
                <v-btn type="submit" :disabled="form.loading" color="primary">
                    Сохранить
                </v-btn>
            </template>
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
                item: null,
                form: null,
                terminateForm: null,
            }
        },
        created() {
            this.terminateForm = this.$ewll.initForm(this, {
                url: `/crud/partnershipSellerAgent/${this.$route.params.id}/terminate`,
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Партнерство расторгнуто',
                success: function () {
                    this.$router.push({name: 'partnershipSellerAgents'});
                }.bind(this),
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.$ewll.initForm(this, {
                    method: 'get',
                    url: `/crud/partnershipSellerAgent/${this.$route.params.id}`,
                    success: function (response) {
                        this.item = response.body;
                        this.$store.commit('breadcrumbs/fill', {agentName: this.item.agentName});
                    }.bind(this),
                }).submit();
                this.$ewll.initForm(this, {
                    method: 'get',
                    url: `/crud/partnershipSellerAgent/${this.$route.params.id}/form`,
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: `/crud/partnershipSellerAgent/${this.$route.params.id}`,
                            definition: response.body,
                            data: response.body.data,
                            snackSuccessMessage: 'Сохранено',
                        });
                    }.bind(this),
                }).submit();
            },
            terminate() {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Расторгнуть партнерство',
                    body: `Расторгнуть партнерство с пользователем ${this.item.agentName}?`,
                }).then(confirmation => {
                    if (confirmation) {
                        this.terminateForm.submit();
                    }
                });
            },
        }
    }
</script>

<style>
</style>
