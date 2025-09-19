function requireAll(require) {
  require.keys().forEach(require);
}
requireAll(require.context(".", true, /\.twig$/));
