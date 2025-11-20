import { defineStore } from "pinia";

/**
 * Store pour gérer l'état de l'interface utilisateur
 */
export const useUiStore = defineStore("ui", {
  state: () => ({
    sidebarOpen: true,
    theme: "light" as "light" | "dark",
    notifications: [] as Array<{
      id: string;
      type: "success" | "error" | "warning" | "info";
      message: string;
      timestamp: Date;
    }>,
  }),

  getters: {
    /**
     * Retourne les notifications triées par date (plus récentes en premier)
     */
    sortedNotifications: (state) => {
      return [...state.notifications].sort(
        (a, b) => b.timestamp.getTime() - a.timestamp.getTime()
      );
    },

    /**
     * Compte le nombre de notifications non lues
     */
    unreadCount: (state) => state.notifications.length,
  },

  actions: {
    /**
     * Bascule l'état de la sidebar
     */
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },

    /**
     * Change le thème de l'application
     */
    setTheme(theme: "light" | "dark") {
      this.theme = theme;
      // Sauvegarder dans localStorage pour persistance
      if (process.client) {
        localStorage.setItem("theme", theme);
      }
    },

    /**
     * Ajoute une notification
     */
    addNotification(
      type: "success" | "error" | "warning" | "info",
      message: string
    ) {
      const notification = {
        id: Date.now().toString(),
        type,
        message,
        timestamp: new Date(),
      };
      this.notifications.push(notification);

      // Auto-suppression après 5 secondes
      setTimeout(() => {
        this.removeNotification(notification.id);
      }, 5000);
    },

    /**
     * Supprime une notification
     */
    removeNotification(id: string) {
      const index = this.notifications.findIndex((n) => n.id === id);
      if (index !== -1) {
        this.notifications.splice(index, 1);
      }
    },

    /**
     * Efface toutes les notifications
     */
    clearNotifications() {
      this.notifications = [];
    },
  },
});
