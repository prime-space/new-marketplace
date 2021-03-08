<template>
    <div class="orderBlockButton">
        <div class="orderBlockButton__tooltipBox">
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn v-on="on" color="red" icon @click="block" :loading="blockForm.loading">
                        <v-icon>mdi-lock</v-icon>
                    </v-btn>
                </template>
                <span>{{ text }}</span>
            </v-tooltip>
        </div>
        <div>
            <div>{{ entityValue }}</div>
        </div>
    </div>
</template>
<script>

    export default {
        props: {
            entityId: Number,
            entityTypeId: Number,
            entityValue: String,
            text: String,
        },
        data: () => ({
            config: config,
            blockForm: null,
        }),
        created() {
            this.blockForm = this.$ewll.initForm(this, {
                method: 'post',
                url: '/crud/customerBlockedEntity',
                data: {
                    entityTypeId: this.entityTypeId,
                    entityId: this.entityId,
                },
                isFormHandleValidationErrors: false,
                success: function (response) {
                    this.$emit('blocked', this.entityTypeId, response.body.id);
                }.bind(this),
            });
        },
        methods: {
            block() {
              this.$store.dispatch('confirmer/ask', {
                title: 'Блокировать',
                body: 'Вы уверены?',
              }).then((confirm) => {
                if (confirm) {
                    this.blockForm.submit();
                }
              });
            },
        }
    }
</script>

<style lang="scss">
.orderBlockButton {
    display: flex;
    flex-direction: row;
    align-items: center;

    &__tooltipBox {
        padding-right: 5px;
    }
}
</style>
