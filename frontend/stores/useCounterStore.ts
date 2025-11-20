import { defineStore } from "pinia";

/**
 * Store d'exemple - Compteur simple
 * Peut être supprimé une fois le projet bien démarré
 */
export const useCounterStore = defineStore("counter", {
  state: () => ({
    count: 0,
  }),

  getters: {
    /**
     * Retourne le double de la valeur du compteur
     */
    doubleCount: (state) => state.count * 2,

    /**
     * Indique si le compteur est pair
     */
    isEven: (state) => state.count % 2 === 0,
  },

  actions: {
    /**
     * Incrémente le compteur de 1
     */
    increment() {
      this.count++;
    },

    /**
     * Décrémente le compteur de 1
     */
    decrement() {
      this.count--;
    },

    /**
     * Réinitialise le compteur à 0
     */
    reset() {
      this.count = 0;
    },

    /**
     * Définit une valeur spécifique
     */
    setCount(value: number) {
      this.count = value;
    },
  },
});
