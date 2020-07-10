<!-- 商品SKU基础模板
elObj：{
type:类型
model:绑定值
api：校验类型
content: 额外校验参数
structure: {dataName:表单名} //层级参数
}
apiCallback=>api校验错误回调
-->
<template>
	<el-input v-else :type="elObj.type" v-model="obj.model" @blur="modelBlur" ref="goods_input"></el-input>
	<!--<el-input v-else :type="elObj.type" v-model="obj.model" @blur="modelBlur" @keyup.native="provingNumber" ref="goods_input"></el-input>-->
</template>

<script>
	import { ZF_syncCheckGoodsExists } from '../../../plugin/api';

	export default {
		props: ['sku', 'elObj'],
		data () {
			return {
				obj: { model: '' },
				$parent: '',
				$root: '',
				confiremed: false
			};
		},
		watch: {
			'obj.model': function (newValue) {
				let $dataName = this.obj.structure && this.obj.structure.dataName;
				let $formData = $dataName ? this.$parent[$dataName] : this.$parent.formData;
				if (this.obj.structure) {
					let { target, index, name } = this.obj.structure;
					if (target && target && name) {
						$formData[target][index][name] = newValue;
					} else if (target && target && !name) {
						$formData[target][index] = newValue;
					}

				} else {
					$formData[this.obj.name || 'goodsSKU'] = newValue;
				}
			},
			'elObj': {
				handler (newValue) {
					if (newValue) {
						this.obj = this.elObj;
					}
				},
				deep: true
			}
		},
		created () {
			this.$parent = this.$vnode.context;
			this.$root = this.$root.$children[0];
		},
		mounted () {
			this.obj = this.elObj;
		},
		complete () {

		},
		methods: {
			modelBlur (target) {
				setTimeout(()=>{
					if (!this.$root.dialogGroup.visible) return false;
					let val = target.target.value;
					let skuList = this.duplicateRemove(val);
					this.obj.model = skuList;
					if(this.confiremed){
						return false;
					}
					this.checkExists(skuList, target);
				},200)
			},
			triggerBlur(){
				let input = this.$refs.goods_input.$refs.input
				if(input){
					let eventObj = document.createEvent('Event')
					eventObj.initEvent('blur',true,true)
					input.dispatchEvent(eventObj)
				}else{
					throw new Error('no goods input')
				}

			},
			async checkExists (skuList, target) {
				if (!skuList) {
					return false;
				}
				this.$root.dialogGroup.submitReady = false;
				let cont = this.elObj.content ? Object.assign(this.elObj.content, { 'pipeline': this.$root.pipeForm.channelModel.toString() }) : {};
				//匹配赠品接口
				if (cont['activityid'] !== undefined) {
					cont['activityid'] = this.obj.model;
				}

				let content = JSON.stringify(cont);
				let params = {
					"page_id": document.getElementById('pageId').value,
					"skus": skuList,
					"pipeline": this.$root.pipeForm.channelModel.toString(),
					"api": this.elObj.api,
					"content": content
				};
				let res = await ZF_syncCheckGoodsExists(params, { messageOff: true, successOff: true });
				if (res.code !== 0) {
					if (res.message.indexOf('SKU') !== -1) {
						this.clearConfirm(skuList, res.message, target);
					} else {
						this.$message({
							message: res.message,
							type: 'warning'
						});
						this.$root.dialogGroup.submitReady = true;
					}

					//匹配赠品接口
					// if (this.elObj.api === 'fullgiftlist' || this.elObj.api === 'increasebuylist') {
					// 	this.$emit('apiCallback');
					// }
					this.$emit('apiCallback');
				} else {
					this.$root.dialogGroup.submitReady = true;
				}
			},
			clearConfirm (skuList, message, target) {
				// let message = 'SKU 222,111 不存在';
				this.confiremed = true
				this.confirm(message + ',是否清空？', () => {
					let delSkuArr = message.split(' ')[1].split(','),
						skuListArr = skuList.split(','),
						newSku;
					delSkuArr.forEach(function (delItem) {
						skuListArr.forEach(function (skuItem, skuIndex) {
							if (delItem === skuItem) {
								skuListArr.splice(skuIndex, 1);
							}
						});
					});
					newSku = skuListArr.toString();
					this.obj.model = newSku;
					this.$root.dialogGroup.submitReady = true;
					setTimeout(()=>{
						this.confiremed = false
					},200)
				}, () => {
					this.$root.dialogGroup.submitReady = true;
					setTimeout(()=>{
						this.confiremed = false
					},200)
				});
			},
			duplicateRemove (skuList) {
				let res = /(\s{5,1000})/g;
				let reg = /\n/g;
				skuList = skuList.replace(res, '').replace(reg, ',');
				if (!skuList) {
					return '';
				}
				let skuArr = skuList.split(',');
				var newArr = [];
				for (var i = 0; i < skuArr.length; i++) {
					if (newArr.indexOf(skuArr[i]) === -1) {
						newArr.push(skuArr[i]);
					}
				}
				skuList = newArr.toString();
				return skuList;
			},
			provingNumber(){
				if(this.obj.type && this.obj.type === 'number'){
					return (/[\d]/.test(String.fromCharCode(event.keyCode)))
				}
			},
			confirm (message, callback, catchFn) {
				this.$confirm(message, '提示', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning',
					customClass:'goods-sku-message'
				}).then(() => {
					if (typeof callback === 'function') {
						callback(this);
					}
				}).catch((err) => {
					if (typeof catchFn === 'function') {
						catchFn(this);
					}
					this.$message({
						type: 'info',
						message: '已取消操作!'
					});
				});
			}
		}
	};
</script>

<style lang="less">
	.goods-sku-message .el-message-box__message p{
		word-break: break-all;
	}
</style>

