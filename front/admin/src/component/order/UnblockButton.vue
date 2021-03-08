<template>
    <div class="orderUnblockButton">
        <div class="orderUnblockButton__tooltipBox">
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn v-on="on" color="success" icon @click="unBlock" :loading="unblockForm.loading">
                        <v-icon>mdi-lock-open-variant</v-icon>
                    </v-btn>
                </template>
                <span>{{ text }}</span>
            </v-tooltip>
        </div>
        <div>
            <div>{{ blockedVia }}</div>
            <div class="red--text">Заблокирован</div>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        blockedVia: String,
        blockedEntityId: Number,
        entityTypeId: Number,
        text: String,
    },
    data: () => ({
        config: config,
        unblockForm: null,
    }),
    created() {
        this.unblockForm = this.$ewll.initForm(this, {
            method: 'delete',
            url: '/crud/customerBlockedEntity/' + this.blockedEntityId,
            isFormHandleValidationErrors: false,
            success: function () {
                this.$emit('unblocked', this.entityTypeId);
            }.bind(this),
        });
    },
    methods: {
        unBlock() {
            this.$store.dispatch('confirmer/ask', {
                title: 'Разблокировать',
                body: 'Вы уверены?',
            }).then((confirm) => {
                if (confirm) {
                    this.unblockForm.submit();
                }
            });
        },
    }
}
</script>

<style lang="scss">
    .orderUnblockButton {
        display: flex;
        flex-direction: row;
        align-items: center;

        &__tooltipBox {
            padding-right: 5px;
        }
    }
</style>
