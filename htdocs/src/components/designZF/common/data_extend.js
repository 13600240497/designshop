import uiLayout from './ui_layout.vue';
import goodsSku from './goods_sku.vue';

export default {
	components: { uiLayout,goodsSku },
	props: ['componentIndex', 'formDataCurrent', 'component_id', 'tab_count'],
	data () {
		return {
			formData: {"goodsSKU":''},
		};
	},
	created () {
	},
	mounted () {
		let formDataCurrent = this.formDataCurrent;
		if (formDataCurrent) {
			this.formData = formDataCurrent;
		}
	},
	methods: {}
};
