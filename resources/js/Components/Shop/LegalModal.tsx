import { useEffect, useRef } from 'react';
import { company, legalDocuments, type LegalDocumentKey } from './legalDocuments';

/**
 * Kebijakan Privasi and Syarat & Ketentuan as a popup, so a shopper in the
 * middle of checkout can read them without leaving the form.
 */
export default function LegalModal({ document: key, onClose }: {
    document: LegalDocumentKey;
    onClose: () => void;
}) {
    const legal = legalDocuments[key];
    const dialogRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        dialogRef.current?.focus();

        const onKeyDown = (event: KeyboardEvent) => event.key === 'Escape' && onClose();
        window.addEventListener('keydown', onKeyDown);

        return () => window.removeEventListener('keydown', onKeyDown);
    }, [onClose]);

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 font-shop"
            onMouseDown={(event) => event.target === event.currentTarget && onClose()}
        >
            <div
                ref={dialogRef}
                role="dialog"
                aria-modal="true"
                aria-labelledby="legal-modal-title"
                tabIndex={-1}
                className="flex max-h-[85vh] w-full max-w-[640px] flex-col rounded-2xl bg-white shadow-xl focus:outline-none"
            >
                <div className="flex items-center justify-between gap-3 border-b border-[#e5e5e5] px-5 py-4">
                    <h2 id="legal-modal-title" className="text-[17px] font-bold">{legal.title}</h2>
                    <button type="button" onClick={onClose} aria-label="Tutup" className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[#707070] hover:bg-[#f5f5f5]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                        </svg>
                    </button>
                </div>

                <div className="overflow-y-auto px-5 py-4 text-[13.5px] leading-6 text-[#333]">
                    {legal.sections.map((section) => (
                        <section key={section.title} className="mb-5">
                            <h3 className="mb-2 text-[15px] font-bold text-black">{section.title}</h3>
                            {section.clauses.map((clause) => (
                                <div key={clause.number} className="mb-2.5">
                                    <p>
                                        <span className="mr-1.5 font-semibold">{clause.number}</span>
                                        {clause.text}
                                    </p>
                                    {clause.items ? (
                                        <ul className="mt-1.5 list-disc space-y-1 pl-9">
                                            {clause.items.map((item) => <li key={item}>{item}</li>)}
                                        </ul>
                                    ) : null}
                                </div>
                            ))}
                        </section>
                    ))}

                    <p className="mb-2">{legal.contactHeading}</p>
                    <address className="not-italic">
                        <p>{company.email}</p>
                        <p>{company.phone}</p>
                        <p className="mt-3 font-semibold">{company.name}</p>
                        <p>{company.address}</p>
                    </address>

                    <p className="mt-5 text-[12px] text-[#707070]">Terakhir diperbarui: {legal.updatedAt}</p>
                </div>

                <div className="border-t border-[#e5e5e5] px-5 py-3">
                    <button type="button" onClick={onClose} className="w-full rounded-[10px] bg-[#1773b0] py-3 text-[14px] font-semibold text-white">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    );
}
