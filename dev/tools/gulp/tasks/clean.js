import del from 'del';
import ThemeRegistry from '../utils/theme-registry';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);

    return del([
        `${themeConfig.path}/**/*`
    ]);
}
