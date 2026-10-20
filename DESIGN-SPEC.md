# SGRH — Design Specification
### Sistema de Gestão de Recursos Humanos · UI/UX Spec for HTML + Custom CSS + Vanilla JS

Target stack: plain HTML, one custom CSS file (`assets/css/app.css`), vanilla JS (no framework, no build step, no CDN, no webfonts). All charts are inline SVG emitted by PHP.

---

## 1. Color System

### 1.1 Brand palette

Calm indigo/violet primary with neutral slate greys. All foreground/background pairs below are WCAG AA (≥ 4.5:1 for text, ≥ 3:1 for UI borders/icons).

| Token | Hex | Use |
|---|---|---|
| `--primary` | `#4F46E5` | Primary actions, active nav, links, focus ring base |
| `--primary-dark` | `#4338CA` | Hover on primary buttons, sidebar active bg |
| `--primary-darker` | `#3730A3` | Pressed / active sidebar text on dark |
| `--primary-light` | `#EEF2FF` | Selected row tint, soft badges, table stripe alt |
| `--primary-light-2` | `#E0E7FF` | Stronger soft bg (chips, hover on soft btn) |
| `--primary-ring` | `#A5B4FC` | Focus ring outer, chart gridline accent |
| `--accent` | `#0EA5E9` | Accent for secondary data series, "em-training" state |
| `--accent-light` | `#E0F2FE` | Accent soft background |
| `--accent-dark` | `#0369A1` | Accent text on light bg |

### 1.2 Status colors

| Token | Hex | Text on it | Soft bg | Soft text | Meaning |
|---|---|---|---|---|---|
| `--success` | `#059669` | `#FFFFFF` | `#D1FAE5` | `#065F46` | Ativo, paid, completed, approved |
| `--success-ink` | `#065F46` | | | | Badge text on soft bg |
| `--warning` | `#D97706` | `#FFFFFF` | `#FEF3C7` | `#92400E` | Férias, pendente, at-risk, due soon |
| `--warning-ink` | `#92400E` | | | | Badge text on soft bg |
| `--danger` | `#DC2626` | `#FFFFFF` | `#FEE2E2` | `#991B1B` | Inativo, atrasado, reprovado, overdue |
| `--danger-ink` | `#991B1B` | | | | Badge text on soft bg |
| `--info` | `#0891B2` | `#FFFFFF` | `#CFFAFE` | `#155E75` | Em treinamento, contrato,Convênio |
| `--info-ink` | `#155E75` | | | | Badge text on soft bg |
| `--neutral` | `#64748B` | `#FFFFFF` | `#F1F5F9` | `#334155` | Rascunho, inativo-neutro |
| `--neutral-ink` | `#334155` | | | | Badge text on soft bg |

Contrast check (AA): `#059669` on `#FFFFFF` = 3.7:1 → **never use for small text alone**; use `--success-ink` `#065F46` on white (6.4:1) or white on `#047857`. Same rule for `#D97706` → use `--warning-ink` `#92400E` on white (6.0:1). `--DC2626` on white = 4.6:1 → OK.

### 1.3 Neutrals (text scale + surfaces)

```
--bg-app:        #F6F7FB   /* page background (cool off-white) */
--bg-subtle:     #F1F5F9   /* table stripes, inset panels, hover row */
--surface:       #FFFFFF   /* cards, modals, tables, topbar */
--surface-raised:#FFFFFF   /* elevated popover / dropdown */
--border:        #E2E8F0   /* default 1px border */
--border-strong: #CBD5E1   /* inputs, table header underline, dividers */
--border-focus: #4F46E5   /* focused input border */

--text-primary:  #0F172A   /* headings 14.9:1 */
--text-secondary:#475569   /* body secondary 7.5:1 */
--text-tertiary: #64748B   /* meta, timestamps 5.3:1 */
--text-disabled:#94A3B8   /* disabled only */
--text-on-dark:  #FFFFFF
--text-on-dark-2:#CBD5E1   /* sidebar inactive label */
```

### 1.4 Sidebar colors (dark variant)

```
--sidebar-bg:        #111827   /* deep slate */
--sidebar-bg-2:      #1F2937   /* hover row */
--sidebar-active-bg: #4F46E5   /* active item pill */
--sidebar-active-tx: #FFFFFF
--sidebar-text:      #CBD5E1   /* inactive  */
--sidebar-text-dim:  #94A3B8   /* section headers */
--sidebar-border:    #1F2937   /* right border / separators */
--sidebar-width: 264px
--sidebar-width-collapsed: 76px
```

### 1.5 Chart palette (ordered, categorical)

```
--chart-1: #4F46E5  indigo
--chart-2: #0EA5E9  sky
--chart-3: #14B8A6  teal
--chart-4: #F59E0B  amber
--chart-5: #EC4899  pink
--chart-6: #8B5CF6  violet
--chart-7: #64748B  slate
--chart-grid: #E2E8F0
--chart-axis: #94A3B8
```

---

## 2. Layout: Sidebar + Topbar

### 2.1 Metrics (px)

```
App shell: display:flex; min-height:100vh; background:var(--bg-app)
Sidebar:  position:fixed; inset-block:0; left:0; width:264px;
         background:var(--sidebar-bg); z-index:60; overflow-y:auto;
         transition: width .2s ease; padding:0; border-right:1px solid var(--sidebar-border)
Sidebar collapsed: width:76px (icons only, label hidden, tooltip on hover)
Main:     margin-left:264px; min-width:0; display:flex; flex-direction:column
Topbar:   position:sticky; top:0; z-index:50; height:64px; background:var(--surface);
         border-bottom:1px solid var(--border); display:flex; align-items:center; gap:16px;
         padding:0 24px
Content:  padding:24px; max-width:1440px; width:100%; margin-inline:auto
```

### 2.2 Sidebar anatomy

- **Brand block** — height 64px (aligns with topbar baseline), logo mark 32×32 rounded 10px (`--primary` gradient), wordmark 16px/700 white, subtitle 11px uppercase letterspacing .06em `--sidebar-text-dim`.
- **Scroll area** — flex:1, padding 12px 12px 24px.
- **Nav section** — label 11px/600 uppercase tracking .08em, `--sidebar-text-dim`, padding 20px 12px 8px.
- **Nav item** — height 40px, border-radius 8px, margin-bottom 2px, display flex, gap 12px, align center, padding 0 12px, font 14px/500 `--sidebar-text`.
  - Icon wrapper 20×20, inline SVG `stroke-width:1.75`, `stroke-linecap:round`, `stroke-linejoin:round`, `fill:none`, `stroke:currentColor`.
  - Active: bg `--sidebar-active-bg`, color white, font-weight 600, plus a **4px × 20px vertical bar** (`position:absolute; left:0; top:10px; border-radius:0 4px 4px 0; background:#A5B4FC`) as the left indicator — on collapsed mode the bar sits at the pill's left edge.
  - Hover (inactive): bg `--sidebar-bg-2`, color `#E2E8F0`, transition 120ms.
  - Focus-visible: `outline:2px solid #A5B4FC; outline-offset:-2px`.
  - Badge count: min-width 20px, height 20px, radius 999px, bg `--danger` (or `--primary`), 11px/700 white, padding 0 6px, margin-left auto.
- **Collapse** — 76px wide; section labels hidden; icons centered; tooltips via `title` attribute or CSS `::after` on hover (dark `#0F172A` bg, 12px white text, radius 6px, offset 8px).
- **Sidebar footer** — user chip (avatar 32px, name 13px/600, role 11px `--sidebar-text-dim`), separated by a 1px `--sidebar-border` line; `margin-top:auto`.

### 2.3 Topbar anatomy

- Left: **hamburger** (mobile only, 40×40, icon 20px), **breadcrumb** (see §4.16), optional page title 15px/600 (desktop only, hidden < 1200px).
- Center: **global search** input — max-width 380px, height 38px, radius 8px, bg `--bg-subtle`, border 1px `--border`, icon 16px `--text-tertiary` left-inset 12px, placeholder 13px `--text-tertiary`; on focus bg white, border `--border-focus`, ring `0 0 0 3px rgba(79,70,229,.15)`. Shortcut hint `⌘K` as a 20px chip right-inset 8px.
- Right: icon buttons (bell with 8px red dot count, help, settings) 38×38 radius 8px, hover bg `--bg-subtle`; **notification badge**; divider 1px `--border` (24px tall); **user menu** = avatar 34px circle + name 13px/600 + chevron 16px.
- Topbar height 64px; on scroll down >8px add shadow `0 1px 3px rgba(15,23,42,.08)`; do not change height (no layout shift).

### 2.4 Responsive behavior

| Breakpoint | Sidebar | Content padding | Grid |
|---|---|---|---|
| `< 768px` (mobile) | Off-canvas: `transform:translateX(-100%)`, width 264px, overlay `rgba(15,23,42,.5)` fade 200ms; body scroll locked when open | 16px | 1 col |
| `768–1199px` (tablet) | Collapsed to 76px by default, expands to overlay on click | 20px | 2 col |
| `≥ 1200px` (desktop) | 264px expanded | 24px | 4 col |
| `≥ 1536px` (wide) | 264px | 24px, max-width 1440px centered | 4 col |

Implement the off-canvas with a single `.sidebar--open` class toggled by vanilla JS + `aria-expanded` on the trigger; no framework. Icons must remain accessible: give each SVG `aria-hidden="true"` and rely on the visible/`.sr-only` text label.

---

## 3. Typography

Font stack (no downloads):
```css
--font-sans: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
--font-mono: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
```

| Token | Size / L.H. | Weight | Notes |
|---|---|---|---|
| `h1` | 24px / 32px | 700 | page title; letter-spacing -.02em |
| `h2` | 20px / 28px | 700 | section title; -.01em |
| `h3` | 17px / 24px | 600 | card title |
| `h4` | 15px / 22px | 600 | table/subsection title |
| `body` | 14px / 21px | 400 | default; `--text-secondary` |
| `body-strong` | 14px / 21px | 600 | labels in table cells |
| `small` | 13px / 19px | 400 | helper text, table meta |
| `label` | 12px / 16px | 600 | form labels, uppercase `.08em` for section eyebrows only |
| `micro` | 11px / 15px | 600 | badges, axis text, timestamps |
| `kpi-value` | 30px / 36px | 700 | KPI number, tabular-nums, letter-spacing -.02em |
| `kpi-label` | 12px / 16px | 600 | uppercase tracking .06em `--text-tertiary` |
| `kpi-delta` | 12px / 16px | 600 | ▲/▼ + %, success/danger |
| `mono` | 13px / 20px | 500 | IDs, CPF, payroll numbers |

All numeric columns and KPI values use `font-variant-numeric: tabular-nums`.

---

## 4. Component Specs

### 4.1 Card
- bg `--surface`; border 1px `--border`; radius 12px; shadow `--shadow-xs`; padding 20px.
- Header: 16px gap, title 17px/600, subtitle 13px `--text-tertiary`, right slot for action (button/link).
- States: `.card--flush` (no padding, used by tables), `.card--accent` (top 3px `--primary` border), `.card--interactive` (hover shadow-md + translateY(-1px), 150ms, pointer cursor).
- Grid helper: `.grid` `display:grid; gap:16px; grid-template-columns:repeat(4,1fr)` → 2 cols <1200px, 1 col <768px.

### 4.2 Stat / KPI card
- Padding 18px 20px; radius 12px; border 1px `--border`; bg white.
- Top row: icon chip 36×36 radius 10px (bg `--primary-light`, icon `--primary` 18px) + optional trend pill.
- Value: 30px/700 `--text-primary`, margin-top 12px.
- Label: 12px/600 uppercase tracking .06em `--text-tertiary`, margin-top 4px.
- Footer: 12px delta (green `--success-ink` ▲ / red `--danger` ▼) + caption 12px `--text-tertiary`, separated by 1px `--border` top, margin-top 14px, padding-top 12px.
- **Sparkline**: inline SVG 100% × 36px, 2px stroke `--primary` round caps, area fill `url(#sparkFade)` = primary 12% → 0%, last point dot r=2.5 fill primary with white 1.5px stroke.
- Variants: `.kpi--success` (chip bg `--success` soft, stroke `--success`), `.kpi--warning`, `.kpi--danger`, `.kpi--info`.

### 4.3 Table
- Wrapper: `.table-wrap` overflow-x auto, border 1px `--border`, radius 12px, bg white.
- `<table>`: width 100%, border-collapse separate, border-spacing 0, font 14px.
- `thead th`: sticky `top:0; z-index:2`, bg `--bg-subtle`, text 12px/600 uppercase tracking .06em `--text-tertiary`, text-align left, padding 12px 16px, border-bottom 1px `--border-strong`, white-space nowrap.
- `tbody td`: padding 14px 16px, border-bottom 1px `--border`, vertical-align middle, `--text-secondary`.
- Striping: `tbody tr:nth-child(even) td { background: #FBFCFE }` (use `#FBFCFE` not `--bg-subtle` to stay subtle).
- Hover: `tbody tr:hover td { background: var(--primary-light) }`.
- Selected row: `tr.is-selected td { background: var(--primary-light-2); box-shadow: inset 3px 0 0 var(--primary) }`.
- Cell with user: avatar 32px + name 14px/600 `--text-primary` + secondary 12px `--text-tertiary`.
- Actions column: right-aligned, buttons 32×32 radius 6px, ghost, icon 16px, hover bg `--bg-subtle`; gap 4px; row-hover reveal optional (`.col-actions { opacity:0 } tr:hover .col-actions { opacity:1 }`).
- Row height default 56px; `.table--compact` 44px (padding 9px 16px).
- Empty row: 64px padding, centered `--text-tertiary` 13px.
- **Mobile transformation (<768px)**: table becomes stacked cards —
  `thead{display:none}`; `tr{display:block; border:1px solid var(--border); border-radius:12px; margin-bottom:12px; padding:12px 14px; background:#fff}`; `td{display:flex; justify-content:space-between; gap:16px; padding:6px 0; border:0}`; each `td` prefixed by a `::before` label from `data-label="..."` attribute (`content:attr(data-label)`), 12px/600 `--text-tertiary`; first cell (primary identity) becomes the card title (full-width, 15px/600 `--text-primary`); action cell full-width with left-aligned buttons.

### 4.4 Form controls
- Label: 13px/600 `--text-secondary`, margin-bottom 6px; required marker `*` in `--danger`.
- Help text: 12px `--text-tertiary`, margin-top 6px.
- Input/select/textarea: height 40px (sm 34px, lg 46px), padding 0 12px, font 14px, bg white, border 1px `--border-strong`, radius 8px, color `--text-primary`, width 100%, transition 120ms.
- Placeholder `--text-disabled`.
- Focus: border `--border-focus` + `box-shadow:0 0 0 3px rgba(79,70,229,.16)`, outline none.
- Hover (unfocused): border `#94A3B8`.
- Error: border `--danger`, bg `#FEF2F2`; focus ring `rgba(220,38,38,.16)`.
- Success: border `--success`.
- Disabled: bg `--bg-subtle`, color `--text-disabled`, cursor not-allowed.
- Read-only: bg `--bg-subtle`, no hover.
- Select: native appearance none, custom 16px chevron SVG right 12px, padding-right 36px.
- Textarea: min-height 96px, padding 10px 12px, resize vertical, line-height 21px.
- Checkbox/radio: 18×18, accent-color `--primary`; custom styled: 18×18 radius 5px, border 1px `--border-strong`, checked bg `--primary` with white check SVG.
- Switch: 40×22 pill, bg `--border-strong`, checked bg `--primary`, knob 18px white, transition 160ms.
- Layout: `.form-grid` 2 cols (gap 16px 20px) → 1 col <768px; `.form-field` `display:flex; flex-direction:column`.
- Label placement: **top-aligned labels** by default; inline left labels only in settings panels (label col 160px) and in filter bars.
- Input group: prefix/suffix addon 40px, bg `--bg-subtle`, border, radius 8px, only outer corners.
- Validation UX: validate on blur + on submit; inline error message 12px `--danger` with 14px alert icon, `aria-describedby` linking input to message; `aria-invalid="true"`; scroll to & focus first invalid field; green check icon on success fields; server errors render at top of form as a danger alert block listing fields.

### 4.5 Buttons
- Base: inline-flex, gap 8px, align center, justify center, font 14px/600, border-radius 8px, border 1px solid transparent, cursor pointer, transition 120ms, white-space nowrap, `text-decoration:none`.
- Sizes: `sm` h32 padding 0 12px font 13px; `md` h40 padding 0 16px; `lg` h46 padding 0 20px font 15px; `icon` 40×40 p0; `icon-sm` 32×32.
- Variants:
  - `primary`: bg `--primary`, color white; hover `--primary-dark`; active `--primary-darker`; shadow `--shadow-xs`.
  - `secondary`: bg white, border `--border-strong`, color `--text-primary`; hover bg `--bg-subtle`.
  - `soft`: bg `--primary-light`, color `--primary-dark`; hover `--primary-light-2`.
  - `ghost`: transparent, color `--text-secondary`; hover bg `--bg-subtle`.
  - `danger`: bg `--danger`, color white; hover `#B91C1C`.
  - `danger-ghost`: transparent, color `--danger`; hover bg `#FEF2F2`.
  - `link`: transparent, color `--primary`, underline on hover, padding 0.
- Loading: 16px spinner SVG (stroke-dasharray animation 700ms linear) replacing the label, `pointer-events:none`, `aria-busy="true"`.
- Disabled: opacity .55, cursor not-allowed.
- Icon-only: must have `aria-label`.
- Groups: `.btn-group` inline-flex with `-webkit-overflow-scrolling`, radius merged to 8px, border collapse.

### 4.6 Badges / status pills
Height 24px, radius 999px, padding 0 10px, font 12px/600, inline-flex gap 6px, align center, border 1px solid transparent.

| Label | Bg | Text | Dot |
|---|---|---|---|
| Ativo | `#D1FAE5` | `#065F46` | `#059669` |
| Inativo | `#FEE2E2` | `#991B1B` | `#DC2626` |
| Férias | `#FEF3C7` | `#92400E` | `#D97706` |
| Atrasado | `#FEE2E2` | `#991B1B` | `#DC2626` (with ⚠ icon) |
| Pendente | `#E0F2FE` | `#155E75` | `#0891B2` |
| Em treinamento | `#EDE9FE` | `#5B21B6` | `#7C3AED` |
| Afastado | `#F1F5F9` | `#334155` | `#64748B` |
| Aprovado | `#D1FAE5` | `#065F46` | `#059669` |
| Reprovado | `#FEE2E2` | `#991B1B` | `#DC2626` |
| Contrato | `#CFFAFE` | `#155E75` | `#0891B2` |

Also: `.badge--solid` (filled bg + white text) for high emphasis; `.badge--outline` (transparent bg, 1px current-ish border). Dot = 6px circle, `margin-right:6px`.

### 4.7 Tabs
- Container: border-bottom 1px `--border`, gap 4px, overflow-x auto.
- Tab: padding 10px 14px, font 14px/600, color `--text-secondary`, border-bottom 2px solid transparent, margin-bottom -1px.
- Active: color `--primary`, border-bottom-color `--primary`.
- Hover (inactive): color `--text-primary`.
- Focus-visible: `outline:2px solid var(--primary); outline-offset:-2px; border-radius:6px 6px 0 0`.
- Tab count chip: 12px pill bg `--bg-subtle`, color `--text-secondary`, margin-left 6px.
- Variant `.tabs--pill`: rounded container bg `--bg-subtle` padding 4px, active = white bg + shadow-xs + radius 8px.

### 4.8 Modal / dialog
- Overlay: `position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(2px); z-index:100; display:flex; align-items:center; justify-content:center; padding:24px`.
- Panel: width 520px (sm 400 / lg 720 / xl 960), max-height 90vh, bg white, radius 14px, shadow `--shadow-2xl`, `overflow:hidden`, display flex column; slide-in animation 160ms `translateY(8px)` + fade.
- Header: 20px 24px padding, h3 17px/600, subtitle 13px `--text-tertiary`; X close button 36×36 ghost right.
- Body: padding 24px, overflow-y auto.
- Footer: padding 16px 24px, border-top 1px `--border`, bg `--bg-subtle`, flex right, gap 8px; sticky at bottom of panel.
- Side drawer variant: panel `height:100vh; max-height:none; border-radius:0; margin-left:auto`, width 480px, slide from right 200ms.
- Accessibility: `role="dialog" aria-modal="true" aria-labelledby`, focus trap, ESC closes, focus returns to trigger, background `inert`/scroll-lock.

### 4.9 Toast / alert
- Container: fixed bottom-right (top-center on mobile), z-index 200, stack gap 8px, max-width 380px.
- Toast: bg `#0F172A`, white text 14px, radius 10px, padding 12px 14px, shadow `--shadow-xl`, icon 18px left, close 16px right, slide-in from right 180ms, auto-dismiss 4s (pause on hover).
- Variants: success icon `--success`/light green `#34D399`, danger `#F87171`, warning `#FBBF24`, info `#60A5FA`.
- **Alerts (inline)**: full-width block, radius 10px, padding 12px 16px, 14px, border-left 4px, bg soft tint, text soft-ink:
  - success `#D1FAE5` / `#065F46`; danger `#FEE2E2` / `#991B1B`; warning `#FEF3C7` / `#92400E`; info `#E0F2FE` / `#155E75`.
  - Icon 18px left, title 14px/600, body 13px.

### 4.10 Pagination
- Wrapper: flex, align center, justify space-between, padding 12px 16px, border-top 1px `--border`, bg white.
- Left: "Mostrando 1–10 de 143" 13px `--text-tertiary`.
- Right: prev/next + page numbers.
- Page button: 34×34, radius 8px, font 13px/600, color `--text-secondary`, hover bg `--bg-subtle`.
- Current: bg `--primary`, color white, radius 8px, min-width 34px.
- Ellipsis: 34×34, transparent, `--text-tertiary`.
- Disabled prev/next: `--text-disabled`, no bg.
- Page size select on the right of the count: 34px height, 13px.
- Mobile: collapse to `‹ 1 de 15 ›` with prev/next only.

### 4.11 Empty state
- Centered block, padding 48px 24px, max-width 420px, margin auto, text-align center.
- Illustration: 96×96 SVG, `--primary-light` bg circle, `--primary` line icon (2px stroke), opacity .9.
- Title 17px/600 `--text-primary`, description 14px `--text-tertiary` margin-top 6px, max 36ch.
- Primary CTA button margin-top 20px; secondary link below (14px, `--primary`).

### 4.12 Progress bar
- Track: height 8px, radius 999px, bg `--bg-subtle`.
- Fill: height 100%, radius 999px, bg `--primary`, width = %, transition width 400ms ease.
- Variants: success `--success`, warning `--warning`, danger `--danger`.
- With label: row above (13px/600 left, 13px/600 right `%`), 6px gap.
- Slim variant 4px for inline table usage.
- Circular variant for onboarding: 44px, stroke 6px, track `--bg-subtle`, stroke-linecap round, rotate -90deg, center label 12px/700.
- Meter for、培训 horas: use `<progress>` styled or div+aria `role="progressbar" aria-valuenow`.

### 4.13 Timeline (onboarding / trilha de carreira)
- Wrapper: position relative, padding-left 28px.
- Spine: 2px line `--border` at `left:9px`, top 6px → bottom 6px.
- Item: position relative, padding-bottom 24px (last child 0).
- Node: 20px circle, bg white, border 2px `--border-strong`, centered on spine (`left:-28px` relative wrapper → `left:-9px` within item). Done: bg `--success`, border white shadow ring `--success` 20%; current: bg `--primary`, border white, pulsing ring (box-shadow 0 0 0 4px rgba(79,70,229,.15)); pending: bg white, border `--border-strong`.
- Content: title 14px/600 `--text-primary`; meta 12px `--text-tertiary` (date · responsible); description 13px `--text-secondary` margin-top 4px; optional badge right.

### 4.14 Avatar
- Sizes: xs 24, sm 32, md 40, lg 56, xl 80. Circle, `overflow:hidden`, bg `--primary-light`, color `--primary-dark`, font 12–16px/600.
- Initials fallback: up to 2 letters, uppercase.
- Image: `object-fit:cover`.
- Status dot: 10px circle, border 2px white, bottom-right; colors from status set.
- Group stack: margin-left -8px, ring 2px white; `+N` chip bg `--bg-subtle`.

### 4.15 Dropdown / menu
- Trigger: any button; menu positioned `position:absolute; right:0; top:calc(100% + 6px)`, min-width 220px, bg white, border 1px `--border`, radius 10px, shadow `--shadow-lg`, padding 6px, z-index 80, fade+scale .98→1 in 120ms.
- Item: 36px height, radius 6px, padding 0 10px, gap 10px, font 14px `--text-secondary`, icon 16px.
- Hover: bg `--bg-subtle`, color `--text-primary`. Active/selected: bg `--primary-light`, color `--primary-dark`, font 600.
- Destructive item: color `--danger`, hover bg `#FEF2F2`.
- Divider: 1px `--border`, margin 6px 0.
- Shortcut hint right-aligned 12px `--text-disabled`.
- Footer group at bottom: border-top, padded 10px, user identity + "Sair".
- Keyboard: ↑/↓ move, Enter select, Esc close, focus trap inside, `role="menu"/"menuitem"`.

### 4.16 Breadcrumb
- Container: flex, align center, gap 8px, font 13px.
- Link: `--text-secondary`, hover `--primary` + underline.
- Current: `--text-primary`, 600, `aria-current="page"`.
- Separator: 16px chevron SVG `--text-disabled`.
- Max 3 levels + `…` overflow.

### 4.17 Sidebar component
Covered in §2.2. Additional: "Sair" item at footer, danger variant on hover; small "Novo" primary mini-button next to "Cadastros" section label (28px height, 12px font).

### 4.18 Filter bar (search + filters toolbar)
- Row: search input 260px + select filters (140px) + date range + right-aligned primary action.
- Sticky under topbar on desktop (`top:64px`, bg `--bg-app` with 8px bottom padding).
- Active filters shown as removable chips: 26px pill, bg `--primary-light`, text `--primary-dark`, 12px, ✕ 12px.
- Below 768px: stacked full-width, filters in a collapsible "Filtros (2)" panel.

---

## 5. UX Rules

### 5.1 Spacing scale (4px base)
`--sp-1:4px · --sp-2:8px · --sp-3:12px · --sp-4:16px · --sp-5:20px · --sp-6:24px · --sp-8:32px · --sp-10:40px · --sp-12:48px · --sp-16:64px`
Gaps: card grid 16px; section 32px; label→control 6px; control→help 6px; buttons 8px; table cells 16px horizontal / 14px vertical; page padding 24px (16 mobile).

### 5.2 Border radius
`--r-xs:4px (chips, small) · --r-sm:6px (icon buttons) · --r-md:8px (inputs, buttons, dropdowns) · --r-lg:12px (cards, tables) · --r-xl:14px (modals) · --r-full:999px (pills, avatars)`.

### 5.3 Shadows
```
--shadow-xs: 0 1px 2px rgba(15,23,42,.06);
--shadow-sm: 0 1px 3px rgba(15,23,42,.10), 0 1px 2px rgba(15,23,42,.06);
--shadow-md: 0 4px 12px rgba(15,23,42,.10);
--shadow-lg: 0 8px 24px rgba(15,23,42,.12);
--shadow-xl: 0 12px 32px rgba(15,23,42,.16);
--shadow-2xl:0 24px 48px rgba(15,23,42,.22);
```
Never use shadows as the only state signal; pair with color/underline.

### 5.4 Responsive
Breakpoints: mobile ≤767px, tablet 768–1199px, desktop ≥1200px, wide ≥1536px. Content max-width 1440px centered. Below 1200px, 4-col KPI grid → 2 cols; below 768px → 1 col. Tables → stacked cards (§4.3). Sidebar → off-canvas (<768px) / collapsed (768–1199px). Modals become full-height sheets on mobile (radius 16px top, bottom sheet). Forms 2→1 col.

### 5.5 Validation & feedback
- Client-side: required, e-mail, CPF/CNPJ mask, date range, numeric min/max; message under field; invalid field gets red border + `aria-invalid`; form scrolls to first error.
- Server-side: render error summary alert at the top of the form; keep user input; field-level errors from server override.
- Success: toast (top-right) + inline green check; redirect after 600ms.
- Toasts: max 3 stacked; auto-dismiss 4s; errors require manual dismiss or 8s.

### 5.6 Loading / skeletons
- Skeleton: bg `#EEF1F6` with a 1.4s linear gradient sweep (`linear-gradient(90deg,#EEF1F6,#F7F9FC,#EEF1F6)`), radius = target radius; `aria-hidden="true"`.
- KPI: 1 line 120×12 + 1 line 64×30. Table rows: 6 rows × 40px with 3 skeleton cells each. Cards: 220×140 block.
- Spinner: 20px SVG circle, 2px stroke, dash animation, centered in empty panels.
- Prefer skeleton on first paint, spinner on button/submit, full-page skeleton on hard navigation.

### 5.7 Accessibility
- Focus visible everywhere: `:focus-visible { outline:2px solid var(--primary); outline-offset:2px; border-radius:4px }` — never `outline:none` without a replacement.
- Skip link: first focusable element, visually hidden until focused, "Pular para o conteúdo", links to `#main`.
- Semantic landmarks: `<header>`, `<nav aria-label="Principal">`, `<main id="main">`, `<aside>`, `<footer>`.
- Icon-only controls need `aria-label`; decorative SVG `aria-hidden="true" focusable="false"`.
- Modals: `role="dialog" aria-modal="true"`, focus trap, Esc, restore focus.
- Tables: `<caption class="sr-only">`, `<th scope="col">`, row header `<th scope="row">`, `aria-sort` on sortable headers.
- Live regions: `aria-live="polite"` for toasts, `role="alert"` for form errors.
- `.sr-only` utility: 1px, clip, absolute.
- Keyboard order follows DOM; dropdown/tabs/menu implement roving focus with JS.
- Minimum target size 40×40 for icon buttons, 44×44 on mobile.
- Contrast: body text ≥4.5:1, large text (≥18.66px bold / 24px) ≥3:1, focus indicators ≥3:1.
- Forms: every input has a `<label for>`; errors via `aria-describedby`.
- Respect `prefers-reduced-motion`: disable transitions/animations.

---

## 6. Charting with Inline SVG (PHP-generated, no JS libs)

### 6.1 Rules
- All charts emitted by PHP as `<svg>` strings with `viewBox` + `preserveAspectRatio="xMidYMid meet"`, `width="100%"` and a fixed `height` attribute.
- No external libraries, no `<canvas>`, no JS redraw. Data prep in PHP (rounding, scaling, max/min, label truncation).
- `role="img"` + `aria-label="..."` and a `<title>`/`<desc>` inside the SVG; a visually-hidden data table alternative for accessibility.
- Text in SVG uses `font-family: inherit` (CSS `svg{font-family:var(--font-sans)}`), `font-size:11`, `fill:var(--text-tertiary)`.

### 6.2 Sizing
| Chart | viewBox | Rendered height | Usage |
|---|---|---|---|
| Donut | `0 0 160 160` | 160px (grid span) | distribuição (by dept/status) |
| Bar (vertical) | `0 0 320 180` | 180px | monthly headcount / entries |
| Bar (horizontal) | `0 0 320 200` | 200px | ranking by department |
| Line | `0 0 480 200` | 200px | absenteeism/turnover trend |
| Sparkline | `0 0 120 36` | 36px | KPI cards |
| Stacked bar | `0 0 320 180` | 180px | headcount by contract type |

### 6.3 Shared visual language
- Gridlines: 1px `--chart-grid` (#E2E8F0), horizontal only, 4–5 lines, dashed for line charts (`stroke-dasharray:3 4`).
- Axis labels: 11px `#64748B`; Y labels right-aligned at `x=32`; X labels centered under ticks.
- Axis line: none (gridlines only). No 3D, no gradients except KPI sparkline.
- Bar corner radius: 4px top corners only (`rx="4"` with a 4px-tall masking rect below, or use `path` with rounded top arcs — recommended: draw `rect` with `rx=4` and cover the bottom 4px with a `rect` of the same fill).
- Bar gap: `barWidth * 0.35` between bars; grouped series gap 2px.
- Line stroke: 2px, `stroke-linecap="round"`, `stroke-linejoin="round"`, no point markers except the last point (r=3, white 1.5px stroke).
- Area under line (optional): fill `url(#areaFade)` = primary 14% → 0% vertical gradient, defined once in a hidden `<svg>` `<defs>` block at the top of the page.
- Donut: `stroke-width:18`, gap 2px between arcs (`stroke-dasharray` computed in PHP as `arc - 2`), rounded caps not used (flat), center label 20px/700 `--text-primary` with 11px caption `--text-tertiary` beneath.
- Legend: HTML below the chart (not SVG) — 12px items, 8px square swatch, 12px gap, wrap.

### 6.4 Chart color rules
- Single-series bar/line: `--chart-1` (#4F46E5).
- Categorical (donut, up to 7): `--chart-1..7` in fixed order; colorblind-safe ordering is indigo → sky → teal → amber → pink → violet → slate.
- Positive/negative in KPI delta charts: `--success` / `--danger`.
- Target line (e.g. headcount goal): 2px `--text-tertiary` dashed with an 11px label "Meta".
- Never rely on color alone: donut segments get a legend + direct labels ≥ 5%; bars get value labels on top when ≤ 8 bars.

### 6.5 Chart containers
- `.chart-card` = card with 20px padding; header (title 15px/600 + subtitle 13px tertiary + right "Ver detalhes" link 13px primary); chart area centered, `max-width:100%`; empty state (48px tall dashed border box with "Sem dados no período" 13px tertiary) when the dataset is empty.

### 6.6 PHP sketch (bar chart)
```php
$w=320;$h=180;$padL=36;$padB=24;$padT=12;
$max = max($maxVal, 1);
$plotH = $h - $padT - $padB; $plotW = $w - $padL - 8;
$bw = intdiv($plotW, count($series) * 2);   // bw = half slot
foreach ($series as $i => $v) {
    $x = $padL + ($i * 2 * $bw) + ($bw * 0.35);
    $bh = (int) round($v / $max * $plotH);
    $y = $padT + $plotH - $bh;
    echo '<rect x="'.$x.'" y="'.$y.'" width="'.$bw.'" height="'.$bh.'" rx="4" fill="#4F46E5"/>';
}
```

### 6.7 Sparkline in KPI card
- Path from PHP over the last 7–12 points, viewBox `0 0 120 36`, scaled with 4px padding, stroke 2px `--primary`, area fill `url(#sparkFade)` (primary 12% → 0%).
- No axis, no labels; tooltips via `<title>` per point (e.g. "Jan: 142").

---

## 7. Design Tokens (`:root`)

```css
:root{
  /* ---- Brand ---- */
  --primary:#4F46E5; --primary-dark:#4338CA; --primary-darker:#3730A3;
  --primary-light:#EEF2FF; --primary-light-2:#E0E7FF; --primary-ring:#A5B4FC;
  --accent:#0EA5E9; --accent-light:#E0F2FE; --accent-dark:#0369A1;

  /* ---- Status ---- */
  --success:#059669; --success-ink:#065F46; --success-soft:#D1FAE5;
  --warning:#D97706; --warning-ink:#92400E; --warning-soft:#FEF3C7;
  --danger:#DC2626;  --danger-ink:#991B1B;  --danger-soft:#FEE2E2;
  --info:#0891B2;    --info-ink:#155E75;    --info-soft:#CFFAFE;
  --neutral:#64748B; --neutral-ink:#334155; --neutral-soft:#F1F5F9;
  --violet:#7C3AED;  --violet-ink:#5B21B6;  --violet-soft:#EDE9FE;

  /* ---- Surfaces ---- */
  --bg-app:#F6F7FB; --bg-subtle:#F1F5F9; --bg-stripe:#FBFCFE; --bg-skeleton:#EEF1F6;
  --surface:#FFFFFF; --surface-raised:#FFFFFF;

  /* ---- Borders ---- */
  --border:#E2E8F0; --border-strong:#CBD5E1; --border-focus:#4F46E5;

  /* ---- Text ---- */
  --text-primary:#0F172A; --text-secondary:#475569; --text-tertiary:#64748B;
  --text-disabled:#94A3B8; --text-on-dark:#FFFFFF; --text-on-dark-2:#CBD5E1;

  /* ---- Sidebar ---- */
  --sidebar-bg:#111827; --sidebar-bg-2:#1F2937; --sidebar-border:#1F2937;
  --sidebar-active-bg:#4F46E5; --sidebar-active-text:#FFFFFF;
  --sidebar-text:#CBD5E1; --sidebar-text-dim:#94A3B8;

  /* ---- Charts ---- */
  --chart-1:#4F46E5; --chart-2:#0EA5E9; --chart-3:#14B8A6; --chart-4:#F59E0B;
  --chart-5:#EC4899; --chart-6:#8B5CF6; --chart-7:#64748B;
  --chart-grid:#E2E8F0; --chart-axis:#94A3B8;

  /* ---- Typography ---- */
  --font-sans:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;
  --font-mono:ui-monospace,SFMono-Regular,"SF Mono",Menlo,Consolas,"Liberation Mono",monospace;
  --fs-h1:24px; --fs-h2:20px; --fs-h3:17px; --fs-h4:15px;
  --fs-body:14px; --fs-small:13px; --fs-label:12px; --fs-micro:11px;
  --fs-kpi:30px;
  --lh-tight:1.2; --lh-body:1.5;

  /* ---- Spacing (4px base) ---- */
  --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px; --sp-5:20px;
  --sp-6:24px; --sp-8:32px; --sp-10:40px; --sp-12:48px; --sp-16:64px;

  /* ---- Radius ---- */
  --r-xs:4px; --r-sm:6px; --r-md:8px; --r-lg:12px; --r-xl:14px; --r-full:999px;

  /* ---- Shadows ---- */
  --shadow-xs:0 1px 2px rgba(15,23,42,.06);
  --shadow-sm:0 1px 3px rgba(15,23,42,.10),0 1px 2px rgba(15,23,42,.06);
  --shadow-md:0 4px 12px rgba(15,23,42,.10);
  --shadow-lg:0 8px 24px rgba(15,23,42,.12);
  --shadow-xl:0 12px 32px rgba(15,23,42,.16);
  --shadow-2xl:0 24px 48px rgba(15,23,42,.22);

  /* ---- Layout ---- */
  --sidebar-width:264px; --sidebar-width-collapsed:76px;
  --topbar-height:64px; --content-max:1440px;
  --icon-stroke:1.75;
}
```

---

## 8. Page Patterns (per module)

| Module | Layout |
|---|---|
| **Dashboard** | Row 1: 4 KPI cards (headcount, turnover %, absenteeism %, vacancies open). Row 2: 2/3 line chart "Absenteísmo 12 meses" + 1/3 donut "Distribuição por departamento". Row 3: table "Próximas férias / vencimentos" (5 cols, status badges) + right panel "Alertas" (alert list with severity dots). Row 4: 3 cards: "Treinamentos em andamento" (progress bars), "Vagas abertas" (list), "Aniversariantes do mês". |
| **Cadastros de colaboradores** | Filter bar + 12-col grid: table of collaborators (avatar, nome, cargo, departamento, status badge, actions). Drawer for detail (tabs: Dados, Frequência, Férias, Documentos, Avaliação). |
| **Frequência** | Month selector + 4 KPIs (presenças, faltas, horas extras, atrasos) + monthly table (matrix: collaborator × day) with colored day cells + summary row; mobile → daily list. |
| **Férias / Ausências** | Year filter + balance KPIs (saldo médio, férias agendadas, dias em falta) + timeline/calendar grid + approval table with Pendente/Aprovado badges and approve/reject actions. |
| **Vagas / Recrutamento** | Pipeline columns (Abertas → Em análise → Entrevistas → Ofertadas → Fechadas) as cards; vacancy table; candidate modal with timeline. |
| **Treinamentos** | Course cards grid (thumbnail 16:9, title, hours, progress bar, badge), filter by category, "Minha capacitação" tab with progress. |
| **Avaliação de desempenho** | Cycle selector + KPI (notas médias), scorecard table with 5 criteria, rating bar visualization, evaluation modal with sliders/stars and comment box. |
| **Documentos** | Upload dropzone card + filterable table (nome, tipo, colaborador, validade, actions) + badge "Vencendo em 30 dias" (warning) / "Vencido" (danger). |
| **Relatórios / Indicadores** | Left filter column (240px: período, departamento, categoria) + right charts area: bar, line, donut, plus an export row (PDF/XLSX buttons). |
| **Login** | Split screen: left brand panel (`--primary` gradient, logo, illustration, 3 bullet features), right form card (max 400px, centered) with e-mail, senha (toggle visibility), "lembrar-me", "esqueci minha senha", submit full-width 46px, SSO buttons (divider "ou entre com" + outlined buttons). Mobile: brand panel hidden, form full screen. |

---

## 9. Iconography
- Library: hand-authored inline SVG sprite (`<symbol>` in a hidden `<svg>` at page top), referenced by `<use href="#icon-x">`. 24×24 viewBox, `fill:none`, `stroke:currentColor`, `stroke-width:1.75`, round caps/joins.
- Rendered size: 20px in nav, 18px in inputs/chips, 16px in buttons/inline, 14px in table action columns.
- ~40 icons needed: dashboard, users, calendar-clock, briefcase, graduation-cap, star, file-text, bar-chart, settings, log-out, search, bell, chevron-down/right/left, plus, filter, download, upload, edit, trash, eye, check, x, alert-triangle, alert-circle, info, menu, more-vertical, arrow-up, arrow-down, sun, moon, shield, user, building, clipboard-list, trending-up, pie-chart.
- Icons never carry meaning alone — always paired with text label or `aria-label`.

---

## 10. Implementation Checklist (no-framework)
1. One CSS file with tokens → base → layout → components → utilities → responsive.
2. Sidebar state via class on `<body>` (`sidebar-collapsed`, `sidebar-open`) + `aria-expanded`; off-canvas overlay div; body scroll lock with `overflow:hidden`.
3. Dropdowns/tabs/menus: ~30 lines of vanilla JS (toggle class, click-outside, Esc, arrow-key roving focus).
4. Modal: single `<dialog>`-like div reused via JS content injection; focus trap + Esc.
5. Toasts: JS-created node, appended to `#toast-root`, auto-remove timer.
6. Table mobile cards: CSS-only using `td::before{content:attr(data-label)}`.
7. Charts: PHP echo of SVG strings; sanitized/escapped; SVG injected into `<div class="chart-card">`.
8. Masks (CPF, phone, currency, date): `input` + vanilla JS `input` event; dates via `<input type="date">`.
9. Lazy-load images with `loading="lazy"`, `decoding="async"`.
10. No `@import` of fonts; no external requests at runtime.
