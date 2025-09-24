import './index.scss';

(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { TextareaControl, Button, Spinner, Notice } = wp.components;
  const { Fragment, useState } = wp.element;
  const { registerPlugin } = wp.plugins;

  const PluginDocumentSettingPanel =
    (wp.editor && wp.editor.PluginDocumentSettingPanel)
      || (wp.editPost && wp.editPost.PluginDocumentSettingPanel);

  const { select, dispatch } = wp.data;
  const apiFetch = wp.apiFetch;
  const NS = (window.WPAI_CONFIG && window.WPAI_CONFIG.namespace) || 'askary-ai/v1';

  // Register AI Q&A Accordion Block.
  registerBlockType('askary/qa-accordion-ai', {
    title: __('AI Q&A Accordion', 'wp-ai-study-assistant'),
    description: __('Generate 5 question/answer pairs from pasted text and render them as an accordion.', 'wp-ai-study-assistant'),
    category: 'widgets',
    icon: 'list-view',
    attributes: {
      sourceText: { type: 'string', default: '' },
      cards: { type: 'array', default: [] }
    },

    edit: (props) => {
      const { attributes, setAttributes } = props;
      const [loading, setLoading] = useState(false);
      const [error, setError] = useState('');

      const onGenerate = async () => {
        setLoading(true);
        setError('');
        try {
          const res = await apiFetch({
            path: `/${NS}/flashcards`,
            method: 'POST',
            data: { text: attributes.sourceText }
          });
          if (res && res.ok && Array.isArray(res.cards)) {
            setAttributes({ cards: res.cards });
          } else {
            setError(res && res.error ? res.error : __('Unexpected response', 'wp-ai-study-assistant'));
          }
        } catch (e) {
          setError(e && e.message ? e.message : String(e));
        } finally {
          setLoading(false);
        }
      };

      return wp.element.createElement(
        Fragment,
        {},
        wp.element.createElement(TextareaControl, {
          label: __('Paste source text', 'wp-ai-study-assistant'),
          help: __('We will generate up to 5 concise Q/A pairs.', 'wp-ai-study-assistant'),
          value: attributes.sourceText,
          onChange: (val) => setAttributes({ sourceText: val }),
          __nextHasNoMarginBottom: true
        }),
        wp.element.createElement(
          Button,
          {
            variant: 'primary',
            onClick: onGenerate,
            disabled: loading || !attributes.sourceText.trim(),
            style: { marginTop: '8px' }
          },
          loading ? __('Generating…', 'wp-ai-study-assistant') : __('Generate Q&A', 'wp-ai-study-assistant')
        ),
        loading && wp.element.createElement(Spinner, {}),
        error && wp.element.createElement(Notice, { status: 'error', isDismissible: true }, error),
        Array.isArray(attributes.cards) &&
          attributes.cards.length > 0 &&
          wp.element.createElement(
            'div',
            { style: { marginTop: '1rem' } },
            attributes.cards.map((c, i) =>
              wp.element.createElement(
                'details',
                { key: i, style: { border: '1px solid #ddd', borderRadius: '6px', padding: '8px', marginBottom: '8px' } },
                wp.element.createElement('summary', {}, (c.q ? String(c.q) : __('Untitled question', 'wp-ai-study-assistant'))),
                wp.element.createElement('div', {}, c.a ? String(c.a) : '')
              )
            )
          )
      );
    },

    save: (props) => {
      const { attributes } = props;
      return wp.element.createElement(
        'div',
        { className: 'wp-block-askary-qa-accordion' },
        Array.isArray(attributes.cards)
          ? attributes.cards.map((c, i) =>
              wp.element.createElement(
                'details',
                { key: i },
                wp.element.createElement('summary', {}, (c.q || '')),
                wp.element.createElement('p', {}, (c.a || ''))
              )
            )
          : null
      );
    }
  });

  // Editor Sidebar: AI Meta Description.
  const MetaPanel = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [ok, setOk] = useState(false);

    const onGenerate = async () => {
      setLoading(true);
      setError('');
      setOk(false);
      try {
        const title = select('core/editor').getEditedPostAttribute('title') || '';
        const content = select('core/editor').getEditedPostContent() || '';
        const res = await apiFetch({ path: `/${NS}/meta`, method: 'POST', data: { title, content } });
        if (res && res.ok && res.meta) {
          dispatch('core/editor').editPost({ excerpt: res.meta });
          setOk(true);
        } else {
          setError(res && res.error ? res.error : __('Unexpected response', 'wp-ai-study-assistant'));
        }
      } catch (e) {
        setError(e && e.message ? e.message : String(e));
      } finally {
        setLoading(false);
      }
    };

    return wp.element.createElement(
      PluginDocumentSettingPanel,
      { name: 'wpai-meta-panel', title: __('AI Meta Description', 'wp-ai-study-assistant') },
      wp.element.createElement('p', {}, __('Generate a concise meta description (<=155 chars) and insert into Excerpt.', 'wp-ai-study-assistant')),
      wp.element.createElement(
        Button,
        { variant: 'secondary', onClick: onGenerate, disabled: loading },
        loading ? __('Generating…', 'wp-ai-study-assistant') : __('Generate', 'wp-ai-study-assistant')
      ),
      ok && wp.element.createElement(Notice, { status: 'success', isDismissible: true }, __('Inserted into Excerpt.', 'wp-ai-study-assistant')),
      error && wp.element.createElement(Notice, { status: 'error', isDismissible: true }, error)
    );
  };

  registerPlugin('wpai-meta-plugin', { render: MetaPanel });
})(window.wp);
