import ThemeRegistry from "../utils/theme-registry";
import { exec } from 'child_process';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);

    exec(`php bin/magento setup:static-content:deploy -f --theme="${themeConfig.name}"`, (err, stdout, stderr) => {
        console.log(stdout);
        console.log(stderr);
        done(err);
    });
}
