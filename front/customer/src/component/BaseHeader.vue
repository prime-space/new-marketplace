<template>
    <div class="baseHeader">
        <div class="baseHeader__left">
            <slot name="left"/>
        </div>
        <div class="baseHeader__right">
            <BaseSelect v-if="isShowCurrencySelect"
                        :options="currencies"
                        :value="config.currency"
                        @change="selectCurrency"
                        style="margin:0 10px"
            >
            </BaseSelect>
            <div class="baseHeader__languages">
                <LanguageSelect
                        :languages="languages"
                        :selected="selectedLanguage"
                        @select="selectLanguage"
                />
            </div>
            <slot name="right"/>
        </div>
    </div>
</template>

<script>
    import BaseSelect from './BaseSelect';
    import LanguageSelect from './LanguageSelect';

    export default {
        components: {BaseSelect, LanguageSelect},
        name: "base-header",
        props: {
            isShowCurrencySelect: {
                type: Boolean,
                required: false,
            },
            value: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                config,
                currencies: [],
                languages: [],
                selectedLanguage: 0,
                setLocaleForm: null,
                setCurrencyForm: null,
            }
        },
        created() {
            this.setLocaleForm = Main.initForm(this, {url: '/setLocale',});
            this.setCurrencyForm = Main.initForm(this, {url: '/setCurrency',});
            for (let i in config.locales) {
                let locale = config.locales[i];
                if (config.locale === locale) {
                    this.selectedLanguage = i - 1;
                }
                this.languages.push({title: locale.toUpperCase(), value: locale});
            }
            for (let i in config.currencies) {
                let currency = config.currencies[i];
                this.currencies.push({title: currency.short.toUpperCase(), value: i});
            }
        },
        methods: {
            selectLanguage(locale) {
                this.setLocaleForm.submit({
                    data: {locale},
                    success: function () {
                        location.reload();
                    },
                });
            },
            selectCurrency(currencyId) {
                currencyId = currencyId - 0;
                this.setCurrencyForm.submit({
                    data: {currencyId},
                    success: function () {
                        this.config.currency = currencyId;
                        this.$emit('currencyChange');
                    }.bind(this),
                });
            }
        }
    };
</script>

<style lang="scss">
    .v-application {
        line-height:inherit!important;
    }
    .baseHeader__languages {
        /*@media screen and (max-width: 900px) {*/
        /*    margin: 30px 0 0;*/
        /*}*/

        /*@media screen and (max-width: 900px) {*/
        /*    display: none;*/
        /*}*/
    }

    .baseHeader {
        background: #0177fd;

        padding: 18px 30px;
        border-radius: 8px;

        display: flex;
        justify-content: space-between;
        align-items: center;

        font-family: "Rubik", sans-serif;
        font-size: 16px;
        color: #fff;
        box-shadow: 0px 20px 20px rgba(1, 119, 253, 0.15);

        @media screen and (max-width: 700px) {
            padding: 18px 15px;
        }

        .baseHeader__left,
        .baseHeader__right {
            display: flex;
            align-items: center;

            img,
            svg {
                padding-right: 10px;
            }

            a {
                color: #fff;
            }
        }
    }
</style>
