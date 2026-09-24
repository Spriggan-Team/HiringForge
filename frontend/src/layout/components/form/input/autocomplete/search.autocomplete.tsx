import React, { useState, useEffect, useRef } from 'react';
import styles from './SearchAutocomplete.module.css';

// SVG imports configurés sous forme de composants React
import CandidateIcon from '/src/assets/svg/menu/candidate-for-elections-svgrepo-com.svg?react';
import type { AutoCompleteSearchResultItem } from '../../../../../features/shared/global';


type SearchAutocompleteProps<T extends AutoCompleteSearchResultItem> = {
  onSearch: (query: string) => Promise<T[]>;
  onSelect: (item: T | null) => void;
  
  debounceMs?: number;
  maxResults?: number;
  placeholder?: string;

  groupBy?: (item: T) => string;
  renderItem?: (item: T) => React.ReactNode;
  initialValue?: T | null;
  
  //-- styles
  className?: string;
}



export function SearchAutocomplete<
  T extends AutoCompleteSearchResultItem
>({
  onSearch,
  onSelect,
  debounceMs = 300,
  maxResults = 5,
  placeholder = "Rechercher...",
  groupBy,
  renderItem,
  initialValue = null,
  className = ""
}: SearchAutocompleteProps<T>) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<T[]>([]);
  
  const [loading, setLoading] = useState(false);
  const [isOpen, setIsOpen] = useState(false);
  const [selectedItem, setSelectedItem] = useState<T | null>(initialValue);
  
  const containerRef = useRef<HTMLDivElement | null>(null);

  // Close dropdown with click
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Debouncing (search)
  useEffect(() => {
    if (!query.trim() || selectedItem) {
      setResults([]);
      setIsOpen(false);
      return;
    }

    setLoading(true);
    const timer = setTimeout(async () => {
      try {
        const data = await onSearch(query);
        setResults(data || []);
        setIsOpen(true);
      }
      catch (err) {
        console.error("Erreur lors de la recherche :", err);
        setResults([]);
      }
      finally {
        setLoading(false);
      }
    }, debounceMs);

    return () => clearTimeout(timer);
  }, [query, debounceMs, onSearch, selectedItem]);

  const handleSelect = (item: T | null) => {
    setSelectedItem(item);
    setIsOpen(false);
    setQuery('');
    onSelect(item);
  };

  const handleClear = () => {
    setSelectedItem(null);
    setQuery('');
    onSelect(null);
  };

  const displayedResults = results.slice(0, maxResults);
  
  // Group By sections
  const groupedResults = groupBy
    ? displayedResults.reduce<Record<string, T[]>>((acc, item) => {
        const group = groupBy(item) || 'Autres';
        if (!acc[group]) 
            acc[group] = [];
        acc[group].push(item);

        return acc;
      }, {})
    : null;

  return (
    <div ref={containerRef} className={`${styles.container} ${className}`}>
      
      {/* SELECTED ITEM  */}
      {selectedItem ? (
        <div className={styles.selectedCard}>
          <div className={styles.selectedContent}>
            {selectedItem.image ? (
              <img
                src={selectedItem.image}
                alt={selectedItem.label}
                className={styles.avatar}
              />
            ) : (
              <CandidateIcon className={styles.svgIcon} />
            )}
            <span className={styles.selectedLabel}>
              {selectedItem.label}
            </span>
          </div>

          <button
            onClick={handleClear}
            className={styles.clearButton}
            type="button"
            aria-label="Effacer la sélection"
          >
            ✕
          </button>
        </div>
      ) : (
        
        /* SEARCH FIELDS */
        <div className={styles.inputWrapper}>
          <div className={styles.searchIcon}>
            {loading ? '…' : <CandidateIcon className={styles.svgIcon} />}
          </div>

          <input
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            onFocus={() => query.trim() && results.length > 0 && setIsOpen(true)}
            placeholder={placeholder}
            className={styles.input}
          />
        </div>
      )}

      {/*  DROPDOWN & RÉSULTATS */}
      {isOpen && !selectedItem && (
        <div className={styles.dropdown}>
          {displayedResults.length === 0 ? (
            <div className={styles.noResults}>
              Aucun résultat trouvé
            </div>
          ) : groupedResults ? (
            
            /* Display by sections */
            Object.entries(groupedResults).map(([group, items], index) => (
              <div key={`${group}_${index}`} className={styles.section}>
                <div className={styles.sectionHeader}>
                  {group}
                </div>
                {items.map((item) => (
                  <ItemRow
                    key={item.id}
                    item={item}
                    onSelect={handleSelect}
                    renderCustom={renderItem}
                  />
                ))}
              </div>
            ))
          ) : (
            
            /*  Simple list */
            <div className={styles.section}>
              {displayedResults.map((item, index) => (
                <ItemRow
                  key={`${item.id}_${index}`}
                  item={item}
                  onSelect={handleSelect}
                  renderCustom={renderItem}
                />
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}

// Sub-component propre et fortement typé
type ItemRowProps<T extends AutoCompleteSearchResultItem> = {
  item: T;
  onSelect: (item: T) => void;
  renderCustom?: (item: T) => React.ReactNode;
}

function ItemRow<T extends AutoCompleteSearchResultItem>({ item, onSelect, renderCustom }: ItemRowProps<T>) {
  if (renderCustom) {
    return (
      <div onClick={() => onSelect(item)} className={styles.itemRowCustom}>
        {renderCustom(item)}
      </div>
    );
  }

  return (
    <div className={styles.itemRow} onClick={() => onSelect(item)}>
      {item.image ? (
        <img src={item.image} alt={item.label} className={styles.avatar} />
      ) : (
        <CandidateIcon className={styles.svgIcon} />
      )}
      <div className={styles.itemText}>
        <span className={styles.itemLabel}>{item.label}</span>
        {item.sublabel && <span className={styles.itemSublabel}>{item.sublabel}</span>}
      </div>
    </div>
  );
}