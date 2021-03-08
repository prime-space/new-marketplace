<template>
    <v-layout v-if="confirmer.active" class="confirmer" row justify-center>
        <v-dialog :value="confirmer.active" persistent max-width="400">
            <v-card>
                <v-card-title class="grey lighten-2">{{confirmer.title}}</v-card-title>
                <v-card-text v-if="confirmer.body">{{confirmer.body}}</v-card-text>
                <v-card-actions>
                    <v-btn @click.native="cancel" text>Отмена</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click.native="confirm" text>Подтвердить</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>

<script>
    export default {
        computed: {
            confirmer() {
                return this.$store.state.confirmer
            }
        },
        methods: {
            confirm() {
                this.confirmer.resolve(true);
                this.$store.commit('confirmer/DEACTIVATE')
            },
            cancel() {
                this.confirmer.resolve(false);
                this.$store.commit('confirmer/DEACTIVATE')
            }
        }
    }
</script>
