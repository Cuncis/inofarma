/**
 * The two legal documents shown as a popup (`LegalModal`). Their text is
 * edited in the admin under Konten Website and loaded from
 * `/konten/dokumen/{key}` when the popup opens; only the keys and the titles
 * shown while it loads live here.
 */
export type LegalDocumentKey = 'privacy' | 'terms';

export const legalDocumentTitles: Record<LegalDocumentKey, string> = {
    privacy: 'Kebijakan Privasi',
    terms: 'Syarat & Ketentuan',
};
