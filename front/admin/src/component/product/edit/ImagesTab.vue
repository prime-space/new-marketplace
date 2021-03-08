<template>
    <div class="productEditImagesTab">
        <span class="title">Фон</span>
        <image-input
                @apply="form.data.backgroundStorageFileId=$event;form.errors.backgroundStorageFileId=null"
                :value="form.data.backgroundStorageFileId"
                :error="form.errors.backgroundStorageFileId"
                class="productEditImagesTab__background"
                :width="1110"
                :height="170"
                :previewWidth="1110"
                :previewHeight="170"
        />
    </div>
</template>

<script>
    import ImageInput from './../../ImageInput';

    export default {
        components: {ImageInput},
        props: {
            form: Object,
        },
        data() {
            return {
                // backgroundForm: null,
            }
        },
        created() {
            // this.backgroundForm = this.$ewll.initForm(this, {
            //     url: '/crud/productBackground/' + this.$route.params.id,
            //     snackSuccessMessage: 'Сохранено',
            //     success: function () {
            //     }.bind(this)
            // });
        },
    }
</script>

<style>
</style>
