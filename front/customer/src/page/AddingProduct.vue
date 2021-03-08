<template>
    <div class="pageAddingProduct">
        <loader isLoading/>
        <BaseHeader>
            <template v-slot:left>
                <i class="mdi mdi-cart mdi-36px"/>
                <span>{{ $t('cart.myCart') }}</span>
            </template>
            <template v-slot:right>
                <!--                <i class="mdi mdi-shield-account mdi-36px"/>-->
                <!--                <span>-->
                <!--          <strong style="text-transform:uppercase;display:block;">{{ $t('cart.guarantee') }}</strong> {{ $t('cart.moneyBack') }}-->
                <!--        </span>-->
            </template>
        </BaseHeader>

        <div class="pageAddingProduct__message">{{ $t('cart.addingProduct') }}...</div>
    </div>
</template>

<script>
    import BaseHeader from './../component/BaseHeader';
    import Loader from './../component/Loader.vue';

    export default {
        components: {
            BaseHeader,
            Loader
        },

        data() {
            return {
                addForm: null,
            };
        },
        created() {
        },
        mounted() {
            let url = '/add/' + this.$route.params.productId;
            if (undefined !== this.$route.params.partnerUserId) {
                url += '/' + this.$route.params.partnerUserId;
            }
            this.addForm = Main.initForm(this, {
                url: url,
                isFormHandleValidationErrors: false,
                success: function () {
                    this.$router.push({name: 'cart'});
                }.bind(this),
                error: function () {
                    //@TODO sentry
                    this.$router.push({name: 'cart'});
                }.bind(this),
            });
            this.init();
        },
        methods: {
            init() {
                setTimeout(function () {
                    this.addForm.submit();
                }.bind(this), 2000);
            },
        }
    };
</script>
<style lang="scss">
    .pageAddingProduct {
        &__message {
            margin: 100px 0 80px 0;
            text-align: center;
            font-size: 18px;
        }
    }
</style>
