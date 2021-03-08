<template>
    <div class="captcha" id="captcha">
        <div class="captcha__content" v-bind:style="{width:size+'px'}">
            <img class="captcha__image" :src="url">
            <v-slider
                    class="captcha__slider"
                    v-model="form.data.captcha"
                    :error-messages="form.errors.captcha"
                    :disabled="disabled"
                    :thumb-size="34"
                    thumb-label="always"
            ></v-slider>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            form: Object,
            disabled: {type: Boolean, default: false},
        },
        data: () => ({
            url: null,
            size: 400,
        }),
        mounted() {
            this.reset();
        },
        watch: {
            'form.errors': {
                handler: function (value) {
                    if (Object.keys(value).length === 0) {
                        this.reset();
                    }
                },
                deep: true
            }
        },
        methods: {
            reset() {
                // this.form.data.captcha = 0; //@TODO не сбрасывает указатель
                let ts = new Date().getTime();
                this.size = document.getElementById('captcha').offsetWidth;
                this.url = '/captcha?size='+this.size+'&'+ts;
            }
        },
    }
</script>

<style>
    .captcha {
        display: flex;
        justify-content: center;
        margin: auto;
        max-width: 800px;
    }
    .captcha__content {
    }

    .captcha__image {
        height: 45px;
    }

    .captcha__slider {
        padding: 0 12px;
        margin: auto;
    }

    .captcha .v-slider__thumb-label {
        color: transparent!important;
    }
    .captcha .v-input__slider {
        margin-top: -12px;
    }
</style>
