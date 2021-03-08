<template>
    <div class="formBody">
        <div v-for="(field,fieldName) in value.definition.fields"
             :key="fieldName"
        >
            <twofa-code-form v-if="fieldName === 'twofaCode'"
                             :form="value"
                             :actionId="config.twofa.actions.addPayout"
                             :isStoredCode="config.twofa.isStoredTwofaCode"
            />
            <v-text-field v-else-if="field.type === 'text'"
                          v-model="value.data[fieldName]"
                          :error-messages="value.errors[fieldName]"
                          :label="field.label"
            />
            <v-text-field v-else-if="field.type === 'number'"
                          v-model="value.data[fieldName]"
                          :error-messages="value.errors[fieldName]"
                          type="number"
                          :label="field.label"
            />
            <v-select v-else-if="field.type === 'choice'"
                      v-model="value.data[fieldName]"
                      :error-messages="value.errors[fieldName]"
                      :items="field.choices"
                      :label="field.label"
            />
            <v-textarea v-else-if="field.type === 'textarea'"
                        v-model="value.data[fieldName]"
                        :error-messages="value.errors[fieldName]"
                        :label="field.label"
                        outlined
            />
            <div v-else>FIELD NOT REALISED!</div>
        </div>
    </div>
</template>

<script>

    import TwofaCodeForm from "../component/TwofaCodeForm";

    export default {
        components: {TwofaCodeForm,},
        props: {
            value: {},
        },
        data() {
            return {
                config: config,
            }
        },
        // this.$emit('input', newValue);
    };
</script>
<style>
</style>
