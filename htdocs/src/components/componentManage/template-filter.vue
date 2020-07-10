<template>
    <el-form :inline="true" :model="formdata">

        <el-form-item label="组件编码">
            <el-input
                v-model="formdata.key"
                maxlength="7"
                @keyup.enter.native="handleSearch"></el-input>
        </el-form-item>
        
        <el-form-item label="中文名称">
            <el-input v-model="formdata.name" @keyup.enter.native="handleSearch"></el-input>
        </el-form-item>

        <el-form-item label="启用状态">
            <el-select v-model="formdata.status" placeholder="请选择">
                <el-option label="所有" value=""></el-option>
                <el-option label="是" value="1"></el-option>
                <el-option label="否" value="0"></el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="应用环境">
            <el-select v-model="formdata.place" placeholder="请选择">
                <el-option label="活动页" value="1"></el-option>
                <el-option label="首页" value="2"></el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="应用站点">
            <el-select v-model="formdata.siteGroup" placeholder="请选择">  
                <el-option
                    v-for="(item, index) in sites"
                    :key="index"
                    :label="item.name"
                    :value="item.code">
                </el-option>
            </el-select>
        </el-form-item>

        <el-button type="primary" @click="handleSearch">搜索</el-button>
        <el-button type="danger" icon="el-icon-plus" @click="handleAdd">新增</el-button>
    </el-form>
</template>

<script>
/**
 * $emit functions:
 * handleAdd 点击新增按钮的回调函数
 * handleConfirm 点击搜索的回调函数
 */
export default {
    props: {
        // 应用站点列表
        sites: {
            type: Array,
            default: []
        }
    },
    data () {
        return {
            formdata: {
                key: '', // 组件唯一编码 U00000
				name: '', // 模版名
				place: '1', // 组件应用环境, 1=活动页，2=首页
				siteGroup: '', // 站点的code, [zf/rg/rw/dl/gb]
                status: '1', // 启用状态，空=所有， 1=启用，0=下线,
            }
        };
    },
    computed: {
        // 获取站点编码列表
        sites () {
            const list = JSON.parse(localStorage.getItem("supportSites")).data;
            list.unshift({
                code: '',
                name: '所有站点'
            });
            return list;
        }
    },
    methods: {
        // 搜索按钮
        handleSearch () {
            this.setQuery('key', this.formdata.key);
            this.$emit('handleSearch');
        },
        // 新增按钮
        handleAdd () {
            this.$emit('handleAdd');
        },

        /**
         * 获取URL参数
         */
        getQuery () {
            const arr = window.location.search.replace('?', '').split('&');
            const query = {};
            arr.map(item => {
                const key =  item.split('=')[0];
                const value = item.split('=')[1];
                query[key] = value;
            });
            return query;
        },
        /**
         * 设置 URL 参数
         * @param {string} key 键值
         * @param {string} value 键值
         */
        setQuery (key, value) {
            const query = this.getQuery();
            query[key] = value;
            const arr = Object.keys(query).map(key => {
                return `${key}=${query[key]}`;
            });
            const pathname = location.pathname;
            const newUrl = `${pathname}?${arr.join('&')}`;
            // 修改地址栏中的地址
            try {
                history.pushState({}, '', newUrl);
            } catch (err) {}
        }
    },

    created () {
        const query = this.getQuery();
        this.formdata.key = query['key'] || '';
    }
}
</script>

<style lang="less" scoped>
    .el-input {
        width: 194px;
    }
</style>
