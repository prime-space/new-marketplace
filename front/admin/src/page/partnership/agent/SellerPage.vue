<template>
    <div class="partnershipAgentSellerPage">
        <v-card  v-if="item">
            <v-card-text>
                <div class="subtitle-1">Связаться</div>
                <div v-if="item.contactTypeId===1">
                    <v-icon>mdi-telegram</v-icon>
                    <a :href="'https://t.me/'+item.contact" target="_blank"> {{item.contact}}</a>
                </div>
                <div v-else-if="item.contactTypeId===2">
                    <v-icon>mdi-skype</v-icon>
                    <a :href="'skype:'+item.contact+'?chat'"> {{item.contact}}</a>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-btn @click.native="terminate" color="error" :disabled="terminateForm.loading" text>
                    Расторгнуть партнерство
                </v-btn>
            </v-card-actions>
        </v-card>
        <card-loading v-else/>
    </div>
</template>

<script>
    import {page} from './../../../mixin/page';
    import CardLoading from "../../../component/CardLoading";

    export default {
        mixins: [page],
        components: {CardLoading},
        data() {
            return {
                item: null,
                terminateForm: null,
            }
        },
        created() {
            this.terminateForm = this.$ewll.initForm(this, {
                url: `/crud/partnershipAgentSeller/${this.$route.params.id}/terminate`,
                isFormHandleValidationErrors: false,
                snackSuccessMessage: 'Партнерство расторгнуто',
                success: function () {
                    this.$router.push({name: 'partnershipAgentSellers'});
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
                    url: `/crud/partnershipAgentSeller/${this.$route.params.id}`,
                    success: function (response) {
                        this.item = response.body;
                        this.$store.commit('breadcrumbs/fill', {sellerName: this.item.sellerName});
                    }.bind(this),
                }).submit();
            },
            terminate() {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Расторгнуть партнерство',
                    body: `Расторгнуть партнерство с пользователем ${this.item.sellerName}?`,
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
