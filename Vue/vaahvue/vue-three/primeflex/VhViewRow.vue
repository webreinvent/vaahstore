<script setup>
import {vaah} from '../../pinia/vaah.js'

const props = defineProps({
    label: {
        type: String,
        default: null
    },
    label_width: {
        type: String,
        default: '150px'
    },
    value:{
        default: null,
    },
    type: {
        type: String,
        default: 'text'
    },
    can_copy:{
        type: Boolean,
        default: false
    }
})

//['label', 'value', 'type']

</script>
<template>
    <tr>
        <td :style="{width: label_width}"><b>{{vaah().toLabel(label)}}</b></td>
        <template v-if="can_copy">
            <td>{{value}}</td>
            <td style="width: 40px;">
                <Button icon="pi pi-copy" @click="vaah().copy(value)" class=" p-button-text"></Button>
            </td>
        </template>
        <template v-else-if="type==='user'">
            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <Button  @click="vaah().copy(value.name)"  class="p-button-outlined p-button-secondary p-button-sm">
                        {{value.name}}
                    </Button>
                </template>

            </td>
        </template>

        <template v-else-if="type==='status'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <Badge v-if="value.slug == 'approved'"  @click="vaah().copy(value.name)" severity="success">
                        {{value.name}}
                    </Badge>
                    <Badge v-else-if="value.slug == 'pending'" @click="vaah().copy(value.name)" severity="warning">
                        {{value.name}}
                    </Badge>
                    <Badge v-else-if="value.slug == 'rejected'" @click="vaah().copy(value.name)" severity="danger">
                        {{value.name}}
                    </Badge>
                    <Badge v-else @click="vaah().copy(value.name)" severity="primary">
                        {{value.name}}
                    </Badge>
                </template>
            </td>
        </template>

        <template v-else-if="type==='multipleCurrency'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                       <Tag :severity="primary" :value="data.name"  style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag>
                        &nbsp;&nbsp;
                    </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='defaultCurrency'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <span>
                        <b>{{value.name}}</b>
                     </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='multipleLingual'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        <Tag :severity="primary" :value="data.name" :rounded="true" style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag> &nbsp;
                    </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='defaultLingual'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value !== null">
                    <span>
                        <b>{{value.name}}</b>
                     </span>
                </template>
                <template v-else><Tag value="No" severity="danger"></Tag></template>
            </td>
        </template>

        <template v-else-if="type==='allowedIps'">
            <td colspan="2" >
                <template v-if="typeof value === 'object' && value && value.length > 0">
                    <span v-for="data in value">
                        <Tag :severity="primary" :value="data" :rounded="true" style="border-radius:20px;padding:5px 10px;"></Tag> &nbsp;
                    </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='userEmail'">

            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <Button  @click="vaah().copy(value.email)"  class="p-button-outlined p-button-secondary p-button-sm">
                        {{value.name}}
                    </Button>
                </template>

            </td>
        </template>

        <template v-else-if="type==='attributeValues'">

            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <span v-for="data in value">
                        <span>
                            {{data.name}}<b>({{data.type}})</b>&nbsp;<br />
                        </span>
                    </span>
                </template>

            </td>
        </template>

        <template v-else-if="type==='productAttributeValues'">

            <td colspan="2" >

                <template v-if="typeof value === 'object' && value !== null">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td v-for="data in value"><div style="word-break: break-word;"><tag>{{data.new_value}}</tag></div></td>
                            </tr>
                        </tbody>
                    </table>
                </template>

            </td>
        </template>

        <template v-else-if="type==='image_preview'">

            <td colspan="2" >

                <Image :src="value"
                       preview
                       alt="Image"
                       width="150" />

            </td>
        </template>

        <template v-else-if="type==='yes-no'">
            <td colspan="2">
                <Badge value="Yes" v-if="value===1 || value=='yes'" severity="success"></Badge>
                <Badge v-else value="No" severity="danger"></Badge>
            </td>
        </template>
        <template v-else-if="type==='description'">
            <td colspan="2">

                <pre style="font-family: Inter, ui-sans-serif, system-ui; white-space: break-spaces;word-break: break-all;">{{value}}</pre>
            </td>
        </template>
        <template v-else-if="type==='multipleValues'">
            <td colspan="2">
                <template v-if="typeof value === 'object' && value !== null">
                    <span v-for="data in value">
                        <Tag class="p-button-outlined p-button-secondary p-button-sm m-1">
                            {{data.value}}
                        </Tag>&nbsp;
                    </span>
                </template>
            </td>
        </template>

        <template v-else-if="type==='description'">
            <td colspan="2">

                <template v-if="value !== null">

                    <pre class="pre-tag"> {{value}}</pre>
                </template>
            </td>
        </template>


        <template v-else>
            <td  colspan="2">{{value}}</td>
        </template>

    </tr>
</template>

<style scoped>

.pre-tag{
font-family: Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,
sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,
Noto Color Emoji;

}

</style>
