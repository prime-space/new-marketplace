<template>
    <v-dialog v-model="dialog" width="500" content-class="productGroupCreateDialog" persistent>
        <template v-slot:activator="{ on }">
            <v-btn v-on="on" @click="form.reset" color="primary" small dark>Новая группа</v-btn>
        </template>
        <v-form v-if="form" @submit.prevent="submit" style="display:contents">
            <v-card>
                <v-card-title class="grey lighten-2">Новая группа</v-card-title>
                <v-card-text>
                    <form-body v-model="form"/>
                    <div class="error--text">
                        <span v-if="form.errors.form">{{ form.errors.form }}</span>
                    </div>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn @click="dialog = false" text>Отменить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn type="submit" color="primary" :disabled="form.loading">Создать</v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
        <card-loading v-else/>
    </v-dialog>
</template>

<script>
    import CardLoading from "./../../../CardLoading";
    import FormBody from "./../../../../form/FormBody";

    export default {
        components: {CardLoading, FormBody,},
        props: {
            formDefinition: Object,
        },
        data: () => ({
            dialog: false,
            form: null,
        }),
        created() {
            this.form = this.$ewll.initForm(this, {
                url: '/crud/partnershipAgentProductGroup',
                definition: this.formDefinition,
                data: this.formDefinition.data,
                success: this.success,
            });
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
            },
            submit() {
                this.form.submit();
            },
            success() {
                this.dialog = false;
                this.$emit('created');
            },
        }
    };
</script>
<style>
</style>
