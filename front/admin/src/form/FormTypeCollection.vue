<template>
    <div>
        <slot/>
        <div v-if="error" class="error--text">{{ error }}</div>
    </div>
</template>

<script>
    export default {
        props: {
            form: Object,
            path: String,
        },
        data() {
            return {
                error: null,
                aPath: null,
            };
        },
        watch: {
            'form.errors': {
                handler: function () {
                    let cursor = this.form.errors;
                    for (let i in this.aPath) {
                        let item = cursor[this.aPath[i]];
                        let isLastIteration = i - 0 === this.aPath.length - 1;
                        if (undefined === item) {
                            break;
                        }
                        if (isLastIteration) {
                            if ('string' !== typeof item) {
                                break;
                            }
                            this.error = item;

                            return;
                        }
                        cursor = item;
                    }
                    this.error = null;
                },
                deep: true,
            },
        },
        created() {
            this.aPath = this.path.split('.');
        },
    };
</script>
<style>
</style>
