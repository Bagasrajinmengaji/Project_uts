/// <reference types="vuetify" />

declare module 'vue' {
    export interface GlobalComponents {
        [key: string]: any;
    }
}

declare module '@vue/runtime-core' {
    export interface GlobalComponents {
        [key: string]: any;
    }
}

export {};
