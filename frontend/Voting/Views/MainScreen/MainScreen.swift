//
//  MainScreen.swift
//  Voting
//
//  Created by Szabolcs Tóth on 15.10.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import SwiftUI

struct MainScreen: View {
    /// Private properties
    @StateObject private var viewModel = QuestionsViewModel(service: QuestionService())
    
    var body: some View {
        content
            .task {
                await viewModel.getQuestions()
            }
    }
    
    @ViewBuilder var content: some View {
        NavigationStack {
            switch viewModel.state {
                
            case .succes(let data):
                List {
                        ForEach(data, id: \.id) { question in
                            NavigationLink {
                                QuestionDetailView(question: question, vote: [])
                            } label: {
                            Text(question.questionText)
                        }
                    }
                }
                .listStyle(PlainListStyle())
                .navigationTitle("Questions")
                
            case .na:
                EmptyView()
                
            case .loading:
                ProgressView()
                
            case .failed(let error):
                ErrorView(errorMessage: error.localizedDescription, viewModel: viewModel)
            }
        }
    }
}

struct MainScreen_Previews: PreviewProvider {
    static var previews: some View {
        MainScreen()
    }
}
