<template>
    <div class="partnershipAgentSettingsPage">
        <form-type-form v-if="form" :form="form">
            <v-checkbox :error-messages="form.errors.isPublicAgent"
                        v-model="form.data.isPublicAgent"
                        label="Быть доступным для предложений продавцов"
            />
            <v-textarea v-model="form.data.agentInfo"
                        :error-messages="form.errors.agentInfo"
                        label="Краткое описание о услугах и методах реализации товаров"
            />
        </form-type-form>
        <card-loading v-else/>
    </div>
</template>

<script>
    import {page} from './../../../mixin/page';
    import CardLoading from './../../../component/CardLoading';
    import FormTypeForm from './../../../form/FormTypeForm';

    export default {
        mixins: [page],
        components: {CardLoading, FormTypeForm},
        data () {
            return {
                config: config,
                form: null,
            }
        },
        mounted(){
            this.init();
        },
        methods: {
            init () {
                this.$ewll.initForm(this, {
                    method: 'get',
                    url: `/crud/agentSettings/${this.config.user.id}/form`,
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: `/crud/agentSettings/${this.config.user.id}`,
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
