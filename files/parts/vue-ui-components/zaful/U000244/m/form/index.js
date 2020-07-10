// 数据配置
const datas = {
    list: {
        title: '可选导航菜单',
        value: null,
        type: 'navigator'
    },
    userGroup: {
        title: '组件展示人群',
        type: 'radio',
        value: '0',
        options: [
            { label: '全部用户', value: '0' },
            { label: '新用户', value: '1' },
            { label: '老用户', value: '2' }
        ]
    }
};

// 样式配置
const styles = {
    bg_color: {
        title: '默认背景色',
        type: 'color',
        value: '#FFFFFF',
        col: 2
    },
    text_color: {
        title: '默认文字颜色',
        type: 'color',
        value: '#333333',
        col: 2
    },
    active_bg_color: {
        title: '选中背景色',
        type: 'color',
        value: '#333333',
        col: 2
    },
    active_text_color: {
        title: '选中文字颜色',
        type: 'color',
        value: '#FFFFFF',
        col: 2
    },
    text_size: {
        title: '文字大小',
        type: 'bar',
        value: 28,
        min: 12,
        max: 100
    },
    height: {
        title: '导航菜单高度',
        type: 'number',
        value: '88'
    },
    padding_left: {
        title: '导航菜单左间距',
        type: 'number',
        value: '24',
        col: 2
    },
    padding_right: {
        title: '导航菜单右间距',
        type: 'number',
        value: '24',
        col: 2
    }
};

import template1 from './template1';

export const config ={
    datas,
    styles,
    layout: [
        template1
    ]
};
