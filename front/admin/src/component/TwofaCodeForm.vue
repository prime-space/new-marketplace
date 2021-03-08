<template>
    <v-text-field prepend-icon="mdi-shield-lock"
                  :error-messages="form.errors.twofaCode"
                  v-model="form.data.twofaCode"
                  label="Код подтверждения"
    >
        <template v-slot:append-outer>
            <v-form v-if="isStoredCode" @submit.prevent="submit">
                <v-btn type="submit" :disabled="codeRequestForm.loading" small>{{btnText}}</v-btn>
            </v-form>
        </template>
    </v-text-field>
</template>

<script>
    export default {
        props: {
            form: Object,
            addFormDataKeys: Array,
            actionId: Number,
            isStoredCode: {type: Boolean, default: false},
        },
        data: () => ({
            config: config,
            btnText: 'Получить код',
            codeRequestForm: null,
            timer: null,
            success: false,
            successMsg: '',
        }),
        created() {
            this.form.errors.twofaCode = null;
            this.codeRequestForm = this.$ewll.initForm(this, {
                snackSuccessMessage: 'Код отправлен',
                success: function () {
                    this.codeRequestForm.loading = true;
                    this.btnText = 59;
                    this.timer = setInterval(this.tick, 1000);
                }.bind(this),
                error: function (response) {
                    if (response.status === 400) {
                        this.$snack.danger({text: this.getFormError(response.body.errors)});
                    }
                    this.btnText = 'Получить код';
                }.bind(this)
            });
        },
        mounted() {
            // if (this.isStoredCode) {
            //     this.submit();
            // }
        },
        watch: {
            'form.resetCount': {
                handler: function () {
                    this.reset();
                }
            },
        },
        methods: {
            submit() {
                this.btnText = 'Отправляем...';
                let data = {};
                for (let i in this.addFormDataKeys) {
                    let key = this.addFormDataKeys[i];
                    data[key] = this.form.data[key];
                }
                this.codeRequestForm.submit({data: data, url: `/2fa/code/${this.actionId}`});
            },
            getFormError(errors) {
                for(let i in errors) {
                    return errors[i];
                }
            },
            tick() {
                if (this.btnText === 1) {
                    this.reset();
                } else {
                    this.btnText--;
                }
            },
            reset() {
                this.btnText = 'Получить код';
                this.codeRequestForm.loading = false;
                clearInterval(this.timer);
            },
        }
    }
</script>
