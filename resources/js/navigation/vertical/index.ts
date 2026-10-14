export interface NavItem {
    title: string;
    to: string;
    icon: string;
    badge?: string;
}

export const navItems: NavItem[] = [
    {
        title: 'Jelajah Berita',
        to: '/',
        icon: 'mdi-newspaper-variant-outline',
    },
    {
        title: 'Berita Saya',
        to: '/my-posts',
        icon: 'mdi-file-document-multiple-outline',
    },
    {
        title: 'Tulis Berita',
        to: '/posts/create',
        icon: 'mdi-pencil-plus-outline',
    },
];

export default navItems;
