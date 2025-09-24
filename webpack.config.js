import path from 'path';
import { fileURLToPath } from 'url';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import TerserPlugin from 'terser-webpack-plugin';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const isProd = process.env.NODE_ENV === 'production';

export default {
  mode: isProd ? 'production' : 'development',
  entry: {
    editor: path.resolve(__dirname, 'assets/src/editor/index.js'),
    public: path.resolve(__dirname, 'assets/src/public/index.js')
  },
  output: {
    path: path.resolve(__dirname, 'assets/dist'),
    filename: '[name].js',
    clean: true
  },
  devtool: isProd ? false : 'source-map',
  module: {
    rules: [
      {
        test: /\.s?css$/i,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { sourceMap: !isProd } },
          { loader: 'postcss-loader', options: { sourceMap: !isProd } },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: !isProd,
              sassOptions: {
                includePaths: [path.resolve(__dirname, 'assets/src')]
              }
            }
          }
        ]
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css'
    })
  ],
  optimization: {
    minimize: isProd,
    minimizer: [
      new TerserPlugin({ extractComments: false }),
      new CssMinimizerPlugin()
    ]
  },
  stats: 'minimal'
};
