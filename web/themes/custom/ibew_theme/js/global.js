/**
 * @file
 * Global Javascript for IBEW Theme.
 */

(function (Drupal) {
    'use strict';

    Drupal.behaviors.ibewGlobal = {
        attach: function (context, settings) {

            // Auto-Link Phone Numbers in Body Content
            // Specific to IBEW text content wrappers to avoid breaking layouts
            const textContainers = context.querySelectorAll('.field--name-body, .ibew-prose, .text-long');

            if (textContainers.length === 0) return;

            // Regex for US phone numbers: (XXX) XXX-XXXX or XXX-XXX-XXXX or 1-XXX-XXX-XXXX or XXX.XXX.XXXX
            // Handles optional +1, parenthesis, dashes, dots, and spaces
            // Excludes detecting dates like 2023-01-01 by enforcing length constraints or lookaheads, 
            // but simpler to just target standard phone formats.
            const phoneRegex = /(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?/gi;

            textContainers.forEach(container => {
                // Use TreeWalker to find text nodes
                const walker = document.createTreeWalker(
                    container,
                    NodeFilter.SHOW_TEXT,
                    {
                        acceptNode: function (node) {
                            // Skip if parent is already an anchor, script, style, form element
                            const parent = node.parentNode;
                            if (
                                parent.tagName === 'A' ||
                                parent.tagName === 'SCRIPT' ||
                                parent.tagName === 'STYLE' ||
                                parent.tagName === 'TEXTAREA' ||
                                parent.tagName === 'INPUT' ||
                                parent.tagName === 'BUTTON' ||
                                parent.isContentEditable
                            ) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            return NodeFilter.FILTER_ACCEPT;
                        }
                    },
                    false
                );

                const nodesToReplace = [];
                let node;
                while (node = walker.nextNode()) {
                    // Check if node has a phone number
                    if (node.nodeValue.match(phoneRegex)) {
                        nodesToReplace.push(node);
                    }
                }

                nodesToReplace.forEach(textNode => {
                    const text = textNode.nodeValue;
                    const fragment = document.createDocumentFragment();
                    let lastIndex = 0;
                    let match;

                    // Must reset regex lastIndex for global searches in loop
                    phoneRegex.lastIndex = 0;

                    let found = false;
                    while ((match = phoneRegex.exec(text)) !== null) {
                        found = true;
                        const before = text.slice(lastIndex, match.index);
                        fragment.appendChild(document.createTextNode(before));

                        const cleanNumber = match[0].replace(/[^\d+]/g, ''); // Strip formatting for tel: link
                        // Verify length to avoid short false positives (should be at least 10 digits for US)
                        if (cleanNumber.length >= 10) {
                            const link = document.createElement('a');
                            link.href = 'tel:' + cleanNumber;
                            link.className = 'ibew-auto-link text-decoration-none';
                            // Add subtle styling or inherit
                            link.style.color = 'inherit';
                            link.style.textDecoration = 'underline';
                            link.textContent = match[0];
                            fragment.appendChild(link);
                        } else {
                            // Fallback if not valid frame
                            fragment.appendChild(document.createTextNode(match[0]));
                        }

                        lastIndex = phoneRegex.lastIndex;
                    }

                    if (found) {
                        const after = text.slice(lastIndex);
                        fragment.appendChild(document.createTextNode(after));
                        textNode.parentNode.replaceChild(fragment, textNode);
                    }
                });
            });

        }
    };
})(Drupal);
