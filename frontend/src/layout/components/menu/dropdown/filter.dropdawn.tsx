import React, { useState, useRef, useEffect } from 'react';
import { ChevronDown, Check, X, Filter } from 'lucide-react';

import styles from "./FilterDropdown.module.css"



export interface FilterOption {
  label: string;
  value: string;
}

//-- Control dropdonw placemnt
export type DropdownPlacement = 'bottom-left' | 'bottom-right' | 'top-left' | 'top-right';
const placementClasses: Record<DropdownPlacement, string> = {
  'bottom-left': styles.bottomLeft,
  'bottom-right': styles.bottomRight,
  'top-left': styles.topLeft,
  'top-right': styles.topRight,
};


//--------------
//-- Components
//--------------

export interface FilterDropdownProps {
  /** label (ex: "Statut", "Département") */
  label: string;
  /** Options */
  options: FilterOption[];
  /** select values */
  selectedValues?: string[];
  /** Authorize multiple selection  */
  multiple?: boolean;
  placement?: DropdownPlacement;

  /** Callback  */
  onChange?: (selectedValues: string[]) => void;

  //-- when confirm or apply si call
  onConfirm?:  (selectedValues: string[]) => void;
}

export const FilterDropdown: React.FC<FilterDropdownProps> = ({
  label,
  options,
  selectedValues = [],
  placement = 'bottom-left',
  multiple = true,
  onChange,
  onConfirm
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Handle click outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  //- Handle select
  const handleSelect = (value: string) => {
    let updatedValues: string[];

    if (multiple) {
      if (selectedValues.includes(value)) {
        updatedValues = selectedValues.filter((v) => v !== value);
      } else {
        updatedValues = [...selectedValues, value];
      }
    } else {
      updatedValues = selectedValues.includes(value) ? [] : [value];
      setIsOpen(false);
    }

    onChange?.(updatedValues);
  };

  //-- handle clear
  const handleClear = (e: React.MouseEvent) => {
    e.stopPropagation();
    onChange?.([]);
  };


  const selectedCount = selectedValues.length;

  return (
    <div className={styles.dropdownContainer} ref={dropdownRef}>
      {/* Trigger Button */}
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className={`${styles.triggerButton} ${selectedCount > 0 ? styles.triggerActive : ''}`}
      >
        <Filter style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
        <span>{label}</span>

        {selectedCount > 0 && (
          <span className={styles.badge}>{selectedCount}</span>
        )}

        {selectedCount > 0 ? (
          <span
            role="button"
            tabIndex={0}
            onClick={handleClear}
            className={styles.clearButton}
          >
            <X style={{ width: '0.875rem', height: '0.875rem' }} />
          </span>
        ) : (
          <ChevronDown
            className={`${styles.chevron} ${isOpen ? styles.chevronOpen : ''}`}
          />
        )}
      </button>

      {/* Menu */}
      {isOpen && (
        <div className={`${styles.menu} ${placementClasses[placement]}`}>
          <div className={styles.menuHeader}>Filtrer par {label}</div>

          <div className={styles.optionsList}>
            {options.map((option) => {
              const isSelected = selectedValues.includes(option.value);
              return (
                <button
                  key={option.value}
                  type="button"
                  onClick={() => handleSelect(option.value)}
                  className={`${styles.optionItem} ${isSelected ? styles.optionSelected : ''}`}
                >
                  <span className={styles.optionLabel}>{option.label}</span>
                  {isSelected && <Check className={styles.checkIcon} />}
                </button>
              );
            })}
          </div>

          {selectedCount > 0 && (
            <div className={styles.menuFooter}>
              <button
                type="button"
                onClick={() => onChange?.([])}
                className={styles.resetBtn}
              >
                Réinitialiser
              </button>
              {/* <button
                type="button"
                onClick={() => { 
                    onConfirm?.(selectedValues);
                    setIsOpen(false);
                }}
                className={styles.applyBtn}
              >
                Appliquer
              </button> */}
            </div>
          )}
        </div>
      )}
    </div>
  );
};