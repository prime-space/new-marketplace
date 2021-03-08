<template>
    <div class="accountPageInfoTab">
        <form-type-form v-if="form" :form="form">
            <v-text-field v-model="form.data.id"
                          disabled
                          label="ID"
            />
            <v-text-field :error-messages="form.errors.nickname"
                          v-model="form.data.nickname"
                          :disabled="form.definition.fields.nickname.disabled"
                          label="Никнейм (изменить невозможно)"
            />
            <v-text-field :error-messages="form.errors.contact"
                          v-model="form.data.contact"
                          label="Контакт (Будет доступен вашим партнерам)"
                          class="accountPageInfoTab__contact"
            >
                <template v-slot:prepend>
                    <v-select :error-messages="form.errors.contactTypeId"
                              v-model="form.data.contactTypeId"
                              :items="form.definition.fields.contactTypeId.choices"
                              style="padding-top:0;margin-top:0"
                              label="Мессенджер"
                    />
                </template>
            </v-text-field>
        </form-type-form>
        <card-loading v-else/>
    </div>
</template>

<script>
    import CardLoading from './../../component/CardLoading';
    import FormTypeForm from './../../form/FormTypeForm';

    export default {
        components: {CardLoading, FormTypeForm,},
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
                    url: `/crud/accountInfo/${this.config.user.id}/form`,
                    success: function (response) {
                        this.form = this.$ewll.initForm(this, {
                            url: `/crud/accountInfo/${this.config.user.id}`,
                            definition: response.body,
                            data: response.body.data,
                            snackSuccessMessage: 'Сохранено',
                            success: function () {
                                this.form.definition.fields.nickname.disabled = true;
                            }.bind(this),
                        });
                    }.bind(this),
                }).submit();
            },
        }
    }
</script>

<style>
    .accountPageInfoTab__contact .v-input__prepend-outer {
        margin-top: 0;
    }
</style>
