/**
 * @author Loic Blascos
 * @since 1.0.0
 */

module.exports = {
	mode: process.env.NODE_ENV,
	entry: __dirname + '/assets/js/src',
	output: {
		path: __dirname + '/assets/js',
		filename: 'build.js',
	},
  	module: {
		rules: [
			{
				test: /\.js$/,
				use: 'babel-loader',
				exclude: /node_modules/,
			},
		]
	},
};
