<template>
    <div class="login">
        <BaseHeader style="margin-bottom:30px">
            <template v-slot:left>
                <i class="mdi mdi-home mdi-36px"/> {{$t('customer.login.auth')}}
            </template>
        </BaseHeader>

        <notification v-if="config.isTokenNotFound" error bordered>{{$t('customer.login.tokenNotFound')}}</notification>

        <BaseContainer style="margin-top:30px">
            <div>
                <div class="loginInfo">
                    <div class="loginInfo__image">
                        <img src="../asset/images/logo.svg" alt="Crocus Pay"/>
                    </div>

                    <div class="loginInfo__text">
                        <p>
                            <span>{{$t('customer.login.dear')}}</span>
                        </p>
                        <div v-if="!isSent">
                            <p>{{$t('customer.login.forAuth1')}}</p>
                            <p>
                                <span>{{$t('customer.login.forAuth2')}}</span>
                            </p>
                        </div>
                        <div v-else>
                            <p><span>{{$t('customer.login.sent')}}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </BaseContainer>
        <div class="authentication" v-if="!isSent">
            <BaseInput placeholder="Email" v-model="form.data.email" :error="form.getError('email')">
                <template v-slot:icon>
                    <i class="mdi mdi-email mdi-inactive mdi-dark"/>
                </template>
            </BaseInput>
            <div class="captcha">
                <captcha :form="form" color="a4b0be"/>
            </div>

            <div class="submit">
                <BaseButton :disabled="form.loading" @click="login">{{$t('customer.login.continue')}}</BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseHeader from "./../component/BaseHeader";
    import BaseContainer from "./../component/BaseContainer";
    import BaseInput from "./../component/BaseInput";
    import BaseButton from "./../component/BaseButton";
    import Notification from "./../component/Notification";
    import Captcha from './../../../../vendor/ewll/user-bundle/front/src/component/Captcha';

    export default {
        components: {
            BaseHeader,
            BaseContainer,
            BaseInput,
            BaseButton,
            Notification,
            Captcha
        },
        data: () => ({
            config: config,
            form: null,
            isSent: false,
        }),
        created() {
            this.form = Main.initForm(this, {
                url: '/login',
                success: function () {
                    this.isSent = true;
                }.bind(this),
            });
        },
        methods: {
            login() {
                this.config.isTokenNotFound = false;
                this.form.submit();
            }
        }
    };
</script>

<style lang="scss" scoped>
    i {
        padding-right: 10px;
        font-size: 20px;
    }

    .loginInfo {
        text-align: center;
        font-size: 15px;

        &__image {
            margin-top: 10px;
            border-bottom: 4px solid #fafafa;
            display: inline-block;
            padding-bottom: 20px;
        }

        &__text {
            margin-top: 26px;

            span {
                font-weight: 500;
            }
        }
    }

    .authentication {
        margin: 55px 0 0;
        padding: 0 70px;

        @media screen and (max-width: 600px) {
            padding: 0 5px;
        }

        .captcha {
            margin-top: 7px;
            display: flex;

            @media screen and (max-width: 600px) {
                display: block;
                text-align: center;
            }

            * {
                flex: 2;
            }

            .captcha__image {
                flex: 1;

                @media screen and (max-width: 600px) {
                    margin: 15px auto 0;
                }
            }
        }

        .submit {
            margin-top: 15px;
        }
    }
</style>
