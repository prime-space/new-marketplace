<template>
    <v-textarea v-model="value"
                :error-messages="error"
                :label="label"
                rows="3"
    />
</template>

<script>
    export default {
        props: {
            form: Object,
            path: String,
            label: String,
        },
        data() {
            return {
                error: null,
                value: null,
                _path: null,
            };
        },
        created() {
            this._path = this.path.split('.');
            this.set(null);
        },
        destroyed() {
            console.log(this._path);
            let cursor = this.form.data;
            for (let i in this._path) {
                let value = {};
                let isLastIteration = i - 0 === this._path.length - 2;
                if (isLastIteration) {
                    console.log(JSON.stringify(cursor));
                    delete(cursor[this._path[i]]);
                    // console.log(this.form.data.productObjects);

                    return;
                }
                if (undefined === cursor[this._path[i]] || isLastIteration) {
                    cursor[this._path[i]] = value;
                }
                cursor = cursor[this._path[i]];
            }
        },
        watch: {
            value(newValue) {
                this.set(newValue);
            },
            'form.errors': {
                handler: function () {
                    let cursor = this.form.errors;
                    for (let i in this._path) {
                        let isLastIteration = i - 0 === this._path.length - 1;
                        if (undefined === cursor[this._path[i]]) {
                            break;
                        }
                        if (isLastIteration) {
                            this.error = cursor[this._path[i]];

                            return;
                        }
                        cursor = cursor[this._path[i]];
                    }
                    this.error = null;
                },
                deep: true,
            },
        },
        methods: {
            set(newValue) {
                let cursor = this.form.data;
                for (let i in this._path) {
                    let value = {};
                    let isLastIteration = i - 0 === this._path.length - 1;
                    if (isLastIteration) {
                        value = newValue;
                    }
                    if (undefined === cursor[this._path[i]] || isLastIteration) {
                        cursor[this._path[i]] = value;
                    }
                    cursor = cursor[this._path[i]];
                }
            }
        },
    };
</script>
<style>
</style>
