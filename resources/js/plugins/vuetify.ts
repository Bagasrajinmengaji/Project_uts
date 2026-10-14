import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

export const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'sneatLight',
        themes: {
            sneatLight: {
                dark: false,
                colors: {
                    primary: '#696CFF',
                    secondary: '#8592A3',
                    success: '#71DD37',
                    info: '#03C3EC',
                    warning: '#FFAB00',
                    error: '#FF3E1D',
                    background: '#F5F5F9',
                    surface: '#FFFFFF',
                    'surface-bright': '#FFFFFF',
                    'surface-light': '#F8F9FA',
                    'on-primary': '#FFFFFF',
                    'on-surface': '#566A7F',
                },
            },
            sneatDark: {
                dark: true,
                colors: {
                    primary: '#696CFF',
                    secondary: '#8592A3',
                    success: '#71DD37',
                    info: '#03C3EC',
                    warning: '#FFAB00',
                    error: '#FF3E1D',
                    background: '#232333',
                    surface: '#2B2C40',
                    'on-primary': '#FFFFFF',
                    'on-surface': '#D5D5E2',
                },
            },
        },
    },
    defaults: {
        VCard: {
            rounded: 'lg',
            elevation: 1,
        },
        VBtn: {
            rounded: 'md',
            flat: true,
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VTextarea: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
        VFileInput: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
        },
    },
});

export default vuetify;
