<template>
	<div class="geshop-we-link">
		<div class="item-1">如发现系统BUG，请提交至
			<a
				href="https://we.gw-ec.com/portal/r/w?cmd=com.awspaas.user.apps.globalegrow.login_userCenter&amp;return_url=Li93P2NtZD1DTElFTlRfQlBNX1dPUktMSVNUX1BST0NFU1NJTlNUX0NSRUFURV9QUkVQQVJFJnRpdGxlPemXrumimOWPjemmiCZJVFRZUEU9SVRQUk9CTEVN"
				target="_blank">WE平台-&gt;IT服务窗-&gt;问题管理 </a><span>（直接点击）,</span>
		</div>
		<div class="item-2">
			<span class="it-phone">IT投诉热线：0755-36807522;</span>
			<span>24小时值班点后： 15889449896</span>
		</div>
	</div>
</template>
<script>
	export default {
		name: 'WeLink',
		data() {
			return {}
		}
	}
</script>
<style lang="less">
	.geshop-we-link {
		display: inline-block;
		vertical-align: middle;
		color: #606266;
		margin-top: 25px;
		a{
			text-decoration: none;
			color:#468fcc;
		}
		.item-1{
			margin-bottom:15px;
		}
		.item-2{
			color: #468fcc;
		}
		.it-phone{
			margin-right: 15px;
		}
	}
</style>
