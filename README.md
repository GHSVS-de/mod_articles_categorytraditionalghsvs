# mod_articles_categorytraditionalghsvs
- Joomla site module from early days but still used on some sites. Whyever.
- Absolutely no idea why it was created.
- I think Joomla changed the `mod_articles_category` somehow and one needed old settings and behavior.
- Now we have to make it compatible with Joomla 4.

 ##

-----------------------------------------------------

# My personal build procedure (WSL 1, Debian, Win 10)

**@since v2022.06.14: Build procedure uses local repo fork of https://github.com/GHSVS-de/buildKramGhsvs**

- Prepare/adapt `./package.json`.
- `cd /mnt/z/git-kram/mod_articles_categorytraditionalghsvs`

## node/npm updates/installation
- `npm run updateCheck` or (faster) `npm outdated`
- `npm run update` (if needed) or (faster) `npm update --save-dev`
- `npm install` (if needed)

## Build installable ZIP package
- `node build.js`
- New, installable ZIP is in `./dist` afterwards.
- All packed files for this ZIP can be seen in `./package`. **But only if you disable deletion of this folder at the end of `build.js`**.s

### For Joomla update and changelog server
- Create new release with new tag.
- - See release description in `dist/release.txt`.
- Extracts(!) of the update and changelog XML for update and changelog servers are in `./dist` as well. Copy/paste and necessary additions.
