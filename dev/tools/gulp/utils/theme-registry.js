import filesRouter from '../../grunt/tools/files-router';

const pubPath = `pub/static`;
const sourcePath = `app/design`;

class ThemeRegistry {
    constructor() {
        this.themes = filesRouter.get('themes');
    }

    getTheme(theme) {
        if (!theme) {
            throw new Error(`Theme not specified`);
        }

        const themeConfig = this.themes[theme];
        if (!themeConfig) {
            throw new Error(`Theme ${theme} not defined`);
        }

        themeConfig.path = `${pubPath}/${themeConfig.area}/${themeConfig.name}/${themeConfig.locale}/`;
        themeConfig.sourcePath  = `${sourcePath}/${themeConfig.area}/${themeConfig.name}/`;
        themeConfig.preprocessorFiles = [];
        themeConfig.sourceFiles = [];
        themeConfig.files.forEach(file => {
            themeConfig.preprocessorFiles.push(`${themeConfig.path}${file}.${themeConfig.dsl}`);
            themeConfig.sourceFiles.push(`${themeConfig.sourcePath}web/${file}.${themeConfig.dsl}`);
        });

        return themeConfig;
    }
}

export default ThemeRegistry;
