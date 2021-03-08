<template>
    <v-form @submit.prevent="_submit" style="display:contents">
        <v-card>
            <v-card-title v-if="title" class="grey lighten-2" primary-title>{{title}}</v-card-title>
            <v-card-text>
                <slot/>
                <div v-if="formError" class="error--text">{{ formError }}</div>
            </v-card-text>
            <v-card-actions>
                <slot name="actions">
                    <v-spacer/>
                    <v-btn type="submit" :disabled="form.loading" color="primary">
                        Сохранить
                    </v-btn>
                </slot>
            </v-card-actions>
        </v-card>
    </v-form>
</template>

<script>
    export default {
        props: {
            form: Object,
            submit: {type: Function, default: null},
            title: {type: String, default: null},
        },
        data() {
            return {
                formError: null,
            }
        },
        watch: {
            'form.errors': {
                handler: function () {
                    this.formError = null;
                    let formErrorFields = ['form', ...Object.keys(this.form.hiddenFields)];
                    for (let i in formErrorFields) {
                        let fieldName = formErrorFields[i];
                        if (this.form.errors[fieldName] !== undefined && this.form.errors[fieldName] !== null) {
                            this.formError = this.form.errors[fieldName];
                        }
                    }
                },
                deep: true,
            },
        },
        methods: {
            _submit() {
                if (null === this.submit) {
                    this.form.submit();
                } else {
                    this.submit();
                }
            },
        },
    };
</script>
<style>
</style>
