import {createApp, markRaw} from 'vue';
import {createPinia, PiniaVuePlugin} from 'pinia'
import PrimeVue from "primevue/config";
import {definePreset} from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

import VueApexCharts from 'vue3-apexcharts';

import '@/index.css'


//-------------PrimeVue Imports
import BadgeDirective from "primevue/badgedirective";
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice'
import Menu from 'primevue/menu';
import ProgressBar from 'primevue/progressbar';
import Ripple from 'primevue/ripple';
import StyleClass from 'primevue/styleclass';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';

import Menubar from "primevue/menubar";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";

import Chip from 'primevue/chip';

import Chart from 'primevue/chart';


import FileUpload from 'primevue/fileupload';

import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import InputGroupAddon from 'primevue/inputgroupaddon';

//-------------/PrimeVue Imports

//-------------CRUD PrimeVue Imports

import Badge from "primevue/badge";
import Button from "primevue/button";
import Panel from "primevue/panel";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Divider from "primevue/divider";
import RadioButton from "primevue/radiobutton";
import Message from "primevue/message";
import Tag from "primevue/tag";

import Dialog from "primevue/dialog";
import Checkbox from "primevue/checkbox";
import ConfirmPopup from "primevue/confirmpopup";
import ToggleButton from "primevue/togglebutton";

//-------------/CRUD PrimeVue Imports


//-------------APP
import App from './layouts/App.vue'
import router from './routes/router'

const app = createApp(App);

const pinia = createPinia();
pinia.use(({store}) => {
    store.$router = markRaw(router)
});
app.use(pinia);
app.use(PiniaVuePlugin);
app.use(router);
//-------------/APP

const vaahstore = definePreset(Aura, {
    semantic: {}
});
//-------------PrimeVue Use
app.use(PrimeVue, {
    pt: {
        datatable: {
            bodyrow: {
                class: '!bg-transparent'
            },
        },
        paginator: {
            first: {
                class: "!rounded-lg !px-2 border-[#D7D9E0]"
            },
            prev: {
                class: "!rounded-lg !px-2 border-[#D7D9E0]"
            },
            next: {
                class: "!rounded-lg !px-2 border-[#D7D9E0]"
            },
            nextIcon: {
                class: ""
            },
            last: {
                class: "!rounded-lg !px-2 border-[#D7D9E0]"
            },
            page: {
                class: "!rounded-lg !px-2 border-[#D7D9E0] bg-transparent"
            },
            pcRowPerPageDropdown: {
                class: "!rounded-lg !px-2 border-[#D7D9E0]"
            },
            current: {
                class: "!border-blue-500 !bg-blue-500/10"
            }
        },
        button: {
            root: {
                class: "!bg-gray-100 !border-gray-200 text-gray-500 rounded-lg border p-1 hover:bg-gray-200"
            }
        },
        badge: {
            root: {
                class: "py-1 px-2 font-bold rounded-full text-[8px] leading-4"
            }
        },
        card: {
            root: {class: '!shadow-card bg-gray-50'},
            body: {class: 'px-2 pt-3 pb-0 gap-0'},
            content: {class: 'p-0'},
            title: {class: '!text-gray-500 font-normal p-0 mb-2 !border-b-0 ml-2'}
        },
        rating: {
            onIcon: {class: '!text-warning-500'}
        },
        panel:{
            root:{class:'bg-gray-50 !border-0'},
            header: {class: '!py-4 px-2 !border-0'},
            content:{class: "!px-2 border-0 bg-gray-50 shadow-[0px 2px 16px 0px #D7D9E0CC]"},
            contentContainer: {class: "border-0 bg-gray-50 shadow-[0px 2px 16px 0px #D7D9E0CC]"}
        },
        accordion: {
            root: { class: '!bg-gray-50 border-none' },
            header: {
                class: 'bg-white border border-gray-200 rounded-lg mb-2 hover:bg-gray-100 transition-colors duration-200',
                style: {
                    boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
                }
            },
            headerAction: {
                class: 'flex items-center justify-between w-full p-3 text-gray-700 font-medium'
            },
            headerIcon: {
                class: 'text-gray-500 ml-auto transition-transform duration-200'
            },
            content: {
                class: 'p-3 bg-white border border-gray-200 rounded-lg shadow-sm text-gray-600'
            }
        },
        accordionTab: {
            root: { class: '!bg-black' },
            header: {
                class: 'rounded-lg'
            },
            headerAction: {
                class: 'flex items-center justify-between w-full p-3 text-gray-700 font-medium'
            },
            headerIcon: {
                class: 'text-gray-500 ml-auto transition-transform duration-200'
            },
            content: {
                class: 'p-3 bg-white border border-gray-200 rounded-lg shadow-sm text-gray-600'
            }
        }
    },
    theme: {
        preset: vaahstore,
        darkMode: false,
        options: {
            darkModeSelector: false,
            darkModeInject: false
        },
    }
});
app.use(ConfirmationService);
app.use(ToastService);
app.use(DialogService);

app.directive('tooltip', Tooltip);
app.directive('badge', BadgeDirective);
app.directive('ripple', Ripple);
app.directive('styleclass', StyleClass);


app.component('ConfirmDialog', ConfirmDialog);
app.component('Menu', Menu);
app.component('ProgressBar', ProgressBar);
app.component('Toast', Toast);
//-------------/PrimeVue Use

// -------------CRUD PrimeVue Use

app.component('Badge', Badge);
app.component('Button', Button);
app.component('Panel', Panel);
app.component('RadioButton', RadioButton);
app.component('InputText', InputText);
app.component('Column', Column);
app.component('Paginator', Paginator);
app.component('Divider', Divider);
app.component('DataTable', DataTable);
app.component('Message', Message);
app.component('Tag', Tag);
app.component('Dialog', Dialog);
app.component('Checkbox', Checkbox);
app.component('ConfirmPopup', ConfirmPopup);
app.component('ToggleButton', ToggleButton);
app.component('Menubar', Menubar);
app.component('Select', Select);
app.component('InputNumber', InputNumber);
app.component('InputGroupAddon', InputGroupAddon);


app.component('FileUpload', FileUpload);

app.component('Accordion', Accordion);
app.component('AccordionTab', AccordionTab);
app.component('AccordionHeader', AccordionHeader);
app.component('AccordionContent', AccordionContent);
app.component('AccordionPanel', AccordionPanel);
app.component('Chart', Chart);

app.component('Tabs', Tabs);
app.component('Tab', Tab);
app.component('TabPanels', TabPanels);
app.component('TabPanel', TabPanel);
app.component('TabList', TabList);

//-------------/CRUD PrimeVue Use

import TreeSelect from 'primevue/treeselect';

app.component('TreeSelect', TreeSelect);


import MultiSelect from 'primevue/multiselect';

app.component('MultiSelect', MultiSelect);


import AutoComplete from 'primevue/autocomplete';

app.component('AutoComplete', AutoComplete);


import InputGroup from "primevue/inputgroup";

app.component("InputGroup", InputGroup);

import ToggleSwitch from "primevue/toggleswitch";

app.component("ToggleSwitch", ToggleSwitch);

import FloatLabel from "primevue/floatlabel";

app.component("FloatLabel", FloatLabel);


import Card from "primevue/card";

app.component("Card", Card);

import Rating from "primevue/rating";

app.component("Rating", Rating);

import Image from "primevue/Image";
import {semantic} from '@primeuix/themes/aura/base';

app.component("Image", Image);


app.component('apexchart', VueApexCharts);


import DatePicker from 'primevue/datepicker';

app.component('DatePicker', DatePicker);

import Textarea from 'primevue/textarea';

app.component('Textarea', Textarea);

import Chips from 'primevue/chips';

app.component('Chips', Chips);

import SelectButton from 'primevue/selectbutton';

app.component('SelectButton', SelectButton);

import Editor from 'primevue/editor';

app.component('Editor', Editor);

import {Icon} from '@iconify/vue';

app.component('Icon', Icon);


import Drawer from 'primevue/drawer';

app.component('Drawer', Drawer);


import DynamicDialog from 'primevue/dynamicdialog';

app.component('DynamicDialog', DynamicDialog);


app.mount('#appStore')


export {app}
