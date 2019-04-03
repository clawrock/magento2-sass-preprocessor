import ThemeRegistry from "../utils/theme-registry";
import { exec } from 'child_process';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);

    exec(`php bin/magento dev:source-theme:deploy --type="${themeConfig.dsl}" --locale="${themeConfig.locale}" --area="${themeConfig.area}" --theme="${themeConfig.name}" ${themeConfig.files.join(' ')}`, (err, stdout, stderr) => {
        console.log(stdout);
        console.log(stderr);
        done(err);
    });
}
