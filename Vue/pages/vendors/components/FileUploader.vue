<script setup>
import {reactive, ref, watch } from 'vue';
import {vaah} from '../../../vaahvue/pinia/vaah'
import { useVendorStore } from '../../../stores/store-vendors'
import axios from 'axios';
/**----------------------
 * Props
 */

const upload_refs = ref([])

const store = useVendorStore();

const temp_setter = ref(store.reset_uploader);

const props = defineProps({
    uploadUrl: {
        type: String,
        required: true
    },
    folderPath: {
        type: String,
        default: 'public/media'
    },
    fileName: {
        type: String,
        default: null
    },
    maxFileSize:{
        type: Number,
        default: 1000000
    },
    file_limit:{
        type: Number,
        default: 5
    },
    can_select_multiple:{
        type: Boolean,
        default: false
    },
    is_basic:{
        type: Boolean,
        default: false
    },
    auto_upload:{
        type: Boolean,
        default: false
    },
    max_file_size:{
        type: Number,
        default: 1000000
    },
    file_type_accept:{
        type: String,
    },
    placeholder:{
        type: String,
        default: 'Upload Image'
    },
    store_label:{
        type: String,
        default: 'avatar'
    }
});

/**----------------------
 * Data
 */
let files = reactive([]);
const emit = defineEmits();
/**----------------------
 * Methods
 */



async function uploadFile(e) {
    let uploaded_files = upload_refs.value.files;

    const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg'];
    const maxSizeMB = 1;

    for (const file of uploaded_files) {
        try {

            if (!file || !file.type || !allowedTypes.includes(file.type) || file.size > maxSizeMB * 1024 * 1024) {
                vaah().toastErrors([(`Invalid file: Only PDF, PNG, JPEG files less than 1MB are allowed.`)]);
                return;

            }

            let formData = new FormData();
            formData.append("file", file);
            formData.append('folder_path', props.folderPath);
            formData.append('file_name', props.fileName);

            let contentType = 'multipart/form-data';

            const res = await axios.post(props.uploadUrl, formData, {
                headers: {
                    'Content-Type': contentType
                }
            });

            upload_refs.value.uploadedFiles[0] = file;
            emit('fileUploaded', res.data);
        } catch (error) {
            console.error(`Error uploading file: ${error.message}`);
        }
    }
    upload_refs.value.files = [];
}


function selectFile (data){

    let temp_file = upload_refs.value.files[upload_refs.value.files.length-1];
    upload_refs.value.files = [];
    upload_refs.value.uploadedFiles = [];
    upload_refs.value.files[0] = temp_file;

}

function removeFile(e){

    // store.item[props.store_label] = null;

}
</script>

<template>
    <FileUpload name="file"
                :auto="auto_upload"
                :accept="file_type_accept"
                ref="upload_refs"
                :mode="is_basic?'basic':'advanced'"
                :multiple="can_select_multiple"
                :customUpload="true"
                @select="selectFile"
                @uploader="uploadFile"
                @removeUploadedFile="removeFile"
                @clear="removeFile"
                :showUploadButton="!auto_upload"
                :showCancelButton="!auto_upload"
                :maxFileSize="max_file_size" >
        <template #empty>
            <div class="flex align-items-center justify-content-center flex-column">
                <p>{{ placeholder }}</p>
            </div>
        </template>
    </FileUpload>
</template>

