Umen TODO file
==============

TODO
----

- Escape only when required on revert (e.g. "[b]" shouldn't escape)
- Remove resolved tags on the fly instead of maintaining trim list [convert-on-the-fly]

DONE
----

- Implement escape sequence, either by:
- Using a special tag "\\(any)", requiring 1-character classes
- Using a global escape sequence when moving cursors, requiring special decoding case
- Make character classes parsing greedy
- Move cursor logics to external function, check for terminal nodes when no
- Fix bug of sample text file
- Differenciate # from * while decoding list tags
- Fix bug: [b]aa[hr]bb[/b]
- Order tags by name, remove when resolved and push to scopes array
- Handle special case for &lt;pre&gt; tag in encoder
- Make first started tag match instead of first ended
- Fix bug: [list]##coin[/list] (first # is ignored)
- Allow parameters with 1+ characters
- Limits should be verified by scanner, not renderer
- Allow multiple matches for same tag
- Implement tokenized string un-parsing
- Escape sequences should be restored by "inverse", not saved
- Empty cycles creates holes in captures
- Add missing yAronet tags and formats (see format.yn.php)
- Plain text escaping should be handled internally: &gt;) should not become &amp;gt;) them &amp;gt[smile]
- Don't add useless escape sequences when using invert conversion
- Parameters capture should be reset only when appropriate
- Escape plain text at rendering only (no need for unescape callback)
- Implement negative character groups
- Allow multiple contexts as a replacement to "+" and "-" suffixes ?
- Make compatible with multi-bytes strings
- Fix tests: revert(convert([b][/b][b])) not working with regexp encoder
- Escape sequence
- Update regular expression patterns
- Converter revert
- Convert & revert callbacks
- Render
- Optimize convert into 1-pass algorithm (queue candidates)
- Introduce new revertable regexp syntax to allow non-capturing patterns
