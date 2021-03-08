<template>
    <div class="imageInput">
        <v-card :style="cardStyle" :outlined="outlined">
            <v-card-text :class="[{'imageInput__imageCard__error': error},'imageInput__imageCard','lighten-5']">
                <img v-if="fitImage!==null" style="" :src="fitImage" width="100%">
                <v-icon v-else x-large>mdi-panorama</v-icon>
                <v-btn
                        absolute
                        dark
                        fab
                        bottom
                        right
                        small
                        color="primary"
                        @click="dialog=true"
                >
                    <v-icon small>mdi-pencil</v-icon>
                </v-btn>
            </v-card-text>
        </v-card>
        <v-dialog v-model="dialog" width="unset" persistent>
            <v-card style="display:inline-table" tile>
                <v-card-title class="grey lighten-2">Загрузка изображения</v-card-title>
                <v-card-text class="text-xs-center">
                    <v-image-input
                            class="imageInput__imageInput"
                            :style="[{width: imageInputWidth+'px'}]"
                            v-model="image"
                            uploadIcon="mdi-upload"
                            rotateClockwiseIcon="mdi-format-rotate-90"
                            rotateCounterClockwiseIcon="mdi-format-rotate-90"
                            flipHorizontallyIcon="mdi-reflect-horizontal"
                            flipVerticallyIcon="mdi-reflect-vertical"
                            clearIcon="mdi-delete-outline"
                            :flipVerticallyIconStyle="{}"
                            :image-quality="0.9"
                            image-format="jpeg"
                            :imageWidth="width"
                            :imageHeight="height"
                            clearable
                    />
                </v-card-text>
                <v-card-actions>
                    <v-btn @click="dialog=false" text>Отмена</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="info" @click="apply" :disabled="image===null"> Применить</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    import VImageInput from 'vuetify-image-input/a-la-carte';

    export default {
        components: {VImageInput,},
        props: {
            error: String,
            outlined: {type: Boolean, default: false},
            value: String,
            width: Number,
            height: Number,
            previewWidth: Number,
            previewHeight: Number,
        },
        watch: {
            'value': {
                handler: function (newValue) {
                    if (newValue === undefined) {
                        this.fitImage = null;
                        this.image = null;
                    } else {
                        this.fitImage = newValue;
                    }
                }
            },
        },
        mounted() {
            this.fitImage = this.value === undefined ? null : this.value;
        },
        data: () => ({
            dialog: false,
            fitImage: null,
            image: null,
        }),
        computed: {
            imageInputWidth() {
                return this.width + 152;
            },
            cardStyle() {
                return [
                    {width: this.previewWidth+'px'},
                    {height: this.previewHeight+'px'},
                    {'min-width': this.previewWidth+'px'},
                    {'min-height': this.previewHeight+'px'},
                ];
            }
        },
        methods: {
            apply() {
                this.dialog = false;
                this.fitImage = this.image;
                this.$emit('apply', this.image);
            }
        },
    };
</script>

<style>
    .imageInput__imageInput {
        /*width: 1000px;*/
    }

    .imageInput__imageCard {
        line-height: 0;
        height: 100%;
        display: flex;
        justify-content: center;
    }
    .imageInput__imageInput > div:first-child > div > div {
        max-width: unset !important;
    }

    .imageInput__imageCard {
        /*height: 136px;*/
        /*height: 140px;*/
        /*width: 250px;*/
        padding: 0 !important;
        text-align: center;
        background-color: #fafafa !important;
        border: 2px dashed transparent !important;
    }

    .imageInput__imageCard__error {
        border: 2px dashed red !important;
    }
</style>
