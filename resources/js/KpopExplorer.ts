import { fromEvent, Subscription, of } from 'rxjs';
import { debounceTime, map, distinctUntilChanged, switchMap, filter, catchError } from 'rxjs/operators';

interface Idol {
    name: string;
    group: string;
    image: string;
}

export class KpopExplorer {
    private searchSubscription?: Subscription;

    constructor(private inputElement: HTMLInputElement, private resultsElement: HTMLElement) {
        this.initSearch();
    }

    private initSearch() {
        // Create an observable from the input event
        this.searchSubscription = fromEvent(this.inputElement, 'input')
            .pipe(
                map((e: Event) => (e.target as HTMLInputElement).value),
                filter(text => text.length > 2 || text.length === 0), // Search if > 2 chars or empty
                debounceTime(400), // Wait for user to stop typing
                distinctUntilChanged(), // Only fetch if value changed
                switchMap(searchTerm => {
                    if (!searchTerm.trim()) return of([]); // Clear results if input is empty
                    return this.fetchIdols(searchTerm);
                }),
                catchError(err => {
                    console.error('Search Stream Error:', err);
                    return of([]); // Keep stream alive on error
                })
            )
            .subscribe({
                next: (data) => this.renderResults(data)
            });
    }

    private async fetchIdols(term: string) {
        const response = await fetch(`/api/kpop?search=${term}`);
        return await response.json();
    }

    private renderResults(idols: Idol[]) {
        this.resultsElement.innerHTML = idols.map(idol => `
            <div class="idol-card">
                <img src="${idol.image}" alt="${idol.name}">
                <p>${idol.name} (${idol.group})</p>
            </div>
        `).join('');
    }

    public destroy() {
        this.searchSubscription?.unsubscribe();
    }
}