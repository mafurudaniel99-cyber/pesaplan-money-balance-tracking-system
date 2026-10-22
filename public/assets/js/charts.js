/* =====================================================================
 * PesaPlan — Charts (vanilla SVG, no libraries)
 * Supports: donut chart (single dataset) and grouped bar chart
 * Reads data from `data-donut` and `data-bars` JSON attributes.
 * Respects prefers-reduced-motion.
 * ===================================================================== */
(function() {
    'use strict';
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Donut chart ---------- */
    function renderDonut(el) {
        let data;
        try { data = JSON.parse(el.getAttribute('data-donut')); }
        catch (e) { return; }
        if (!data || !data.length) return;

        const total = data.reduce(function(s, d) { return s + (d.value || 0); }, 0);
        if (total <= 0) return;

        const size = 180;
        const cx = size / 2;
        const cy = size / 2;
        const r = 70;
        const innerR = 44;
        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('viewBox', '0 0 ' + size + ' ' + size);
        svg.setAttribute('width', '100%');
        svg.setAttribute('height', '100%');
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', 'Chat ya duara ya matumizi kwa kategoria');

        let angle = -Math.PI / 2; // start at top
        data.forEach(function(d) {
            const slice = (d.value / total) * Math.PI * 2;
            const a0 = angle;
            const a1 = angle + slice;
            const large = slice > Math.PI ? 1 : 0;
            const x0 = cx + r * Math.cos(a0);
            const y0 = cy + r * Math.sin(a0);
            const x1 = cx + r * Math.cos(a1);
            const y1 = cy + r * Math.sin(a1);
            const xi1 = cx + innerR * Math.cos(a1);
            const yi1 = cy + innerR * Math.sin(a1);
            const xi0 = cx + innerR * Math.cos(a0);
            const yi0 = cy + innerR * Math.sin(a0);
            const path = document.createElementNS(ns, 'path');
            path.setAttribute('d',
                'M ' + x0 + ' ' + y0 +
                ' A ' + r + ' ' + r + ' 0 ' + large + ' 1 ' + x1 + ' ' + y1 +
                ' L ' + xi1 + ' ' + yi1 +
                ' A ' + innerR + ' ' + innerR + ' 0 ' + large + ' 0 ' + xi0 + ' ' + yi0 +
                ' Z'
            );
            path.setAttribute('fill', d.color || '#4a6fa5');
            path.setAttribute('stroke', '#fff');
            path.setAttribute('stroke-width', '1.5');
            const title = document.createElementNS(ns, 'title');
            title.textContent = d.label + ': ' + formatTZS(d.value);
            path.appendChild(title);
            svg.appendChild(path);
            angle = a1;
        });

        // Center label
        const centerLabel = document.createElementNS(ns, 'text');
        centerLabel.setAttribute('x', cx);
        centerLabel.setAttribute('y', cy - 2);
        centerLabel.setAttribute('text-anchor', 'middle');
        centerLabel.setAttribute('font-size', '11');
        centerLabel.setAttribute('font-weight', '600');
        centerLabel.setAttribute('fill', '#5a6670');
        centerLabel.textContent = 'Jumla';
        svg.appendChild(centerLabel);
        const centerValue = document.createElementNS(ns, 'text');
        centerValue.setAttribute('x', cx);
        centerValue.setAttribute('y', cy + 12);
        centerValue.setAttribute('text-anchor', 'middle');
        centerValue.setAttribute('font-size', '12');
        centerValue.setAttribute('font-weight', '700');
        centerValue.setAttribute('fill', '#1a2228');
        centerValue.textContent = formatTZS(total);
        svg.appendChild(centerValue);

        el.innerHTML = '';
        el.appendChild(svg);
    }

    /* ---------- Grouped bar chart (income vs expense) ---------- */
    function renderBars(el) {
        let data;
        try { data = JSON.parse(el.getAttribute('data-bars')); }
        catch (e) { return; }
        if (!data || !data.length) return;

        const W = 360, H = 240;
        const padding = { top: 20, right: 16, bottom: 32, left: 48 };
        const chartW = W - padding.left - padding.right;
        const chartH = H - padding.top - padding.bottom;

        const maxVal = Math.max(1, ...data.flatMap(function(d) { return [d.income || 0, d.expense || 0]; }));
        // Round to nice number
        const niceMax = Math.ceil(maxVal / 100000) * 100000;

        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
        svg.setAttribute('width', '100%');
        svg.setAttribute('height', '100%');
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', 'Chat ya mistari ya mapato na matumizi');

        // Y axis gridlines
        for (let i = 0; i <= 4; i++) {
            const y = padding.top + (chartH / 4) * i;
            const line = document.createElementNS(ns, 'line');
            line.setAttribute('x1', padding.left);
            line.setAttribute('x2', W - padding.right);
            line.setAttribute('y1', y);
            line.setAttribute('y2', y);
            line.setAttribute('stroke', '#e9edf0');
            line.setAttribute('stroke-width', '1');
            svg.appendChild(line);

            const label = document.createElementNS(ns, 'text');
            label.setAttribute('x', padding.left - 6);
            label.setAttribute('y', y + 4);
            label.setAttribute('text-anchor', 'end');
            label.setAttribute('font-size', '10');
            label.setAttribute('fill', '#7e8a94');
            label.textContent = formatShort(niceMax * (1 - i / 4));
            svg.appendChild(label);
        }

        // Bars
        const groupWidth = chartW / data.length;
        const barWidth = Math.min(28, groupWidth / 3);
        data.forEach(function(d, i) {
            const groupX = padding.left + groupWidth * i + groupWidth / 2;

            // Income bar
            const incH = (d.income / niceMax) * chartH;
            const incBar = document.createElementNS(ns, 'rect');
            incBar.setAttribute('x', groupX - barWidth - 2);
            incBar.setAttribute('y', padding.top + chartH - incH);
            incBar.setAttribute('width', barWidth);
            incBar.setAttribute('height', incH);
            incBar.setAttribute('fill', '#16a085');
            incBar.setAttribute('rx', '3');
            const incTitle = document.createElementNS(ns, 'title');
            incTitle.textContent = 'Mapato ' + d.label + ': ' + formatTZS(d.income);
            incBar.appendChild(incTitle);
            svg.appendChild(incBar);

            // Expense bar
            const expH = (d.expense / niceMax) * chartH;
            const expBar = document.createElementNS(ns, 'rect');
            expBar.setAttribute('x', groupX + 2);
            expBar.setAttribute('y', padding.top + chartH - expH);
            expBar.setAttribute('width', barWidth);
            expBar.setAttribute('height', expH);
            expBar.setAttribute('fill', '#c0392b');
            expBar.setAttribute('rx', '3');
            const expTitle = document.createElementNS(ns, 'title');
            expTitle.textContent = 'Matumizi ' + d.label + ': ' + formatTZS(d.expense);
            expBar.appendChild(expTitle);
            svg.appendChild(expBar);

            // X label
            const lbl = document.createElementNS(ns, 'text');
            lbl.setAttribute('x', groupX);
            lbl.setAttribute('y', H - padding.bottom + 16);
            lbl.setAttribute('text-anchor', 'middle');
            lbl.setAttribute('font-size', '10');
            lbl.setAttribute('fill', '#5a6670');
            lbl.textContent = d.label;
            svg.appendChild(lbl);
        });

        // Legend
        const legendData = [
            { color: '#16a085', label: 'Mapato' },
            { color: '#c0392b', label: 'Matumizi' },
        ];
        legendData.forEach(function(l, i) {
            const sw = document.createElementNS(ns, 'rect');
            sw.setAttribute('x', padding.left + i * 80);
            sw.setAttribute('y', 6);
            sw.setAttribute('width', 10);
            sw.setAttribute('height', 10);
            sw.setAttribute('rx', '2');
            sw.setAttribute('fill', l.color);
            svg.appendChild(sw);
            const t = document.createElementNS(ns, 'text');
            t.setAttribute('x', padding.left + i * 80 + 14);
            t.setAttribute('y', 15);
            t.setAttribute('font-size', '11');
            t.setAttribute('fill', '#5a6670');
            t.textContent = l.label;
            svg.appendChild(t);
        });

        el.innerHTML = '';
        el.appendChild(svg);
    }

    function formatTZS(v) {
        return Math.round(v).toLocaleString('en-US') + ' TZS';
    }
    function formatShort(v) {
        if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
        if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
        return Math.round(v).toString();
    }

    // Render all
    document.querySelectorAll('[data-donut]').forEach(renderDonut);
    document.querySelectorAll('[data-bars]').forEach(renderBars);
})();
