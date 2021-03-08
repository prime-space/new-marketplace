<template>
    <div class="accountPageApiTab">
        <form-type-form v-if="form" :form="form">
            <v-text-field prepend-icon="mdi-key"
                          :error-messages="form.errors.apiKey"
                          v-model="form.data.apiKey"
                          label="Ключ (Скрывается)"
            >
                <template v-slot:append-outer>
                    <v-btn @click="generateApiKey" small>Генерировать</v-btn>
                </template>
            </v-text-field>
            <twofa-code-form :form="form" :actionId="config.twofa.actions.saveApiSettings"
                             :isStoredCode="config.twofa.isStoredTwofaCode"
            />
            <v-checkbox :error-messages="form.errors.isApiEnabled"
                        v-model="form.data.isApiEnabled"
                        label="Задействовать API"/>
        </form-type-form>
        <card-loading v-else/>
    </div>
</template>

<script>
    import CardLoading from './../../component/CardLoading';
    import FormTypeForm from './../../form/FormTypeForm';
    import TwofaCodeForm from './../TwofaCodeForm';

    export default {
        components: {CardLoading, FormTypeForm, TwofaCodeForm},
        data() {
            return {
                config: config,
                tab: null,
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
                    url: `/crud/apiSettings/${this.config.user.id}/form`,
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: `/crud/apiSettings/${this.config.user.id}`,
                            definition: response.body,
                            data: response.body.data,
                            snackSuccessMessage: 'Сохранено',
                        });
                    }.bind(this),
                }).submit();
            },
            generateApiKey() {
                let chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                this.form.data.apiKey = '';
                for (let i = 0; i < 64; i++) {
                    this.form.data.apiKey += chars.charAt(Math.floor(Math.random() * chars.length));
                }
            },
        }
    }
</script>

<style>
</style>
