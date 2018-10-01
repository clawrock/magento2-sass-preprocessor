import filesRouter from '../../grunt/tools/files-router';
import config from '../config';

class ThemeRegistry {
    constructor() {
        this.themes = filesRouter.get('themes');
    }

    getTheme(theme) {
        if (!theme) {
            return false;
        }

        const themeConfig = this.themes[theme];
        if (!themeConfig) {
            throw new Error(`Theme ${theme} not defined`);
        }

        themeConfig.path = `${config.projectPath}/pub/static/${themeConfig.area}/${themeConfig.name}/${themeConfig.locale}/`;
        themeConfig.preprocessorFiles = [];
        themeConfig.files.forEach(file => {
            themeConfig.preprocessorFiles.push(`${themeConfig.path}${file}.${themeConfig.dsl}`);
        });

        return themeConfig;
    }

    getThemeKeyByFile(path) {
        const re = new RegExp('frontend+\/([^\/]+)+\/([^\/]+)');
        const result = path.match(re);
        let foundTheme = false;

        if (result && result[1] && result[2]) {
            Object.keys(this.themes).forEach(theme => {
                if (this.themes[theme].name === `${result[1]}/${result[2]}`) {
                    foundTheme = theme;
                }
            });
        }

        return foundTheme;
    }
}

export default ThemeRegistry;
